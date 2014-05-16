#!/bin/sh

TMP_DECRYPT_DIR=/dev/shm/byt/

find $TMP_DECRYPT_DIR -type f -cmin +1 -delete
