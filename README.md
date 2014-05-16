Byt
===

Byt is a simple encrypted file hosting framework.
You can easily install it on your own server, or use our public service available on: http://byt.tl/

Short link
-------------------

To create one, make a call to `/shorter.php?url=you_url`
The url should contain 'http(s)://' or it will be marked as invalid
You will have a json answer with the generated link:
`{"url":"http://byt.tl/f1d41e25"}`

File upload
-------------------

To upload a file, make a POST call to `/upload.php`, the file must be contained in a field 'file'
You will have a json answer with the generated link an key:
`{"link":"http://byt.tl/f2f1ed21", "key":"0caa103d", "filename":"08079543.jpg"}`

Warning: you will never be abled to retriev the key of an upload file.
