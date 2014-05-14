#!/usr/bin/env python

import sys
import urllib2
import mimetypes
import random
import string
import tarfile
import os

upload_url = "http://byt.tl/upload.php"

def usage():
    print("Usage: %s __file__" % sys.argv[0])
    sys.exit(1)

"""Encode multipart form data to upload files via POST."""


_BOUNDARY_CHARS = string.digits + string.ascii_letters

def encode_multipart(fields, files, boundary=None):
    r"""Encode dict of form fields and dict of files as multipart/form-data.
    Return tuple of (body_string, headers_dict). Each value in files is a dict
    with required keys 'filename' and 'content', and optional 'mimetype' (if
    not specified, tries to guess mime type or uses 'application/octet-stream').

    >>> body, headers = encode_multipart({'FIELD': 'VALUE'},
    ...                                  {'FILE': {'filename': 'F.TXT', 'content': 'CONTENT'}},
    ...                                  boundary='BOUNDARY')
    >>> print('\n'.join(repr(l) for l in body.split('\r\n')))
    '--BOUNDARY'
    'Content-Disposition: form-data; name="FIELD"'
    ''
    'VALUE'
    '--BOUNDARY'
    'Content-Disposition: form-data; name="FILE"; filename="F.TXT"'
    'Content-Type: text/plain'
    ''
    'CONTENT'
    '--BOUNDARY--'
    ''
    >>> print(sorted(headers.items()))
    [('Content-Length', '193'), ('Content-Type', 'multipart/form-data; boundary=BOUNDARY')]
    >>> len(body)
    193
    """
    def escape_quote(s):
        return s.replace('"', '\\"')

    if boundary is None:
        boundary = ''.join(random.choice(_BOUNDARY_CHARS) for i in range(30))
    lines = []

    for name, value in fields.items():
        lines.extend((
            '--{0}'.format(boundary),
            'Content-Disposition: form-data; name="{0}"'.format(escape_quote(name)),
            '',
            str(value),
        ))

    for name, value in files.items():
        filename = value['filename']
        if 'mimetype' in value:
            mimetype = value['mimetype']
        else:
            mimetype = mimetypes.guess_type(filename)[0] or 'application/octet-stream'
        lines.extend((
            '--{0}'.format(boundary),
            'Content-Disposition: form-data; name="{0}"; filename="{1}"'.format(
                    escape_quote(name), escape_quote(filename)),
            'Content-Type: {0}'.format(mimetype),
            '',
            value['content'],
        ))

    lines.extend((
        '--{0}--'.format(boundary),
        '',
    ))
    body = '\r\n'.join(lines)

    headers = {
        'Content-Type': 'multipart/form-data; boundary={0}'.format(boundary),
        'Content-Length': str(len(body)),
    }

    return (body, headers)

def get_file_list(dirr):

    files = []
    def do_dir(dirr, files_list):
        for root, directories, files in os.walk(dirr):
            for filename in files:
                files_list.append(os.path.join(root, filename))
            for dirname in directories:
                do_dir(os.path.join(root, dirname), files)

    do_dir(dirr, files)
    return files

def make_tarball():

    tarball_name = "/tmp/%s.tar" % os.urandom(6).encode('hex')
    tar = tarfile.open(tarball_name, "w")
    target = sys.argv[1]
    if os.path.isdir(target):
        files = get_file_list(target)
        for filename in files:
            tar.add(filename)
    elif os.path.isfile(target):
        tar.add(target)

    tar.close()

    return tarball_name

def encrypt_file(filepath):
    from subprocess import call

    key = os.urandom(12).encode('hex')
    command = "/usr/bin/openssl enc -a -aes-256-cbc -in %s -out %s.aes -k %s" % (filepath, filepath, key)
    call(command, shell=True);

    return ("%s.aes" % filepath, key)


if __name__ == "__main__":
    if len(sys.argv) < 2:
        usage()

    tarball = make_tarball()
    try:
        path, key = encrypt_file(tarball)
    except OSError as e:
        print("Openssl: %s" % str(e))
        print("No openssl found, create a raw tarball (Y/n) ?")
        if "-f" in sys.argv:
            print("-f option given, assume Y")
        else:
            c = sys.stdin.read(1)
            if c not in ['Y', 'y']:
                print("Abort upload...")
                os.remove(tarball);
                sys.exit(0)

        path = tarball
        key = ""


    try:
        fil = open(path, 'rb')
        content = fil.read()
        fil.close()
    except Exception as e:
        print("Error: %s" % str(e))
        sys.exit(1)

    fields = {}
    files = {'file': {'filename':path, 'content':content}}

    data, headers = encode_multipart(fields, files)

    req = urllib2.Request(upload_url, data=data, headers=headers)
    f = urllib2.urlopen(req)

    os.remove(tarball);
    if path != tarball:
        os.remove(path);

    if len(key) != 0:
        print("wget " + f.read() + " -O- -q | openssl enc -d -a -aes-256-cbc -k %s | tar xvf -" % key)
        
    else:
        print("wget " + f.read() + " -O- -q | tar xvf -")



