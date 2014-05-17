#!/bin/sh

TMP_DECRYPT_DIR=/dev/shm/byt/
EXPIRE_CLEANUP_SCRIPT=./clean_expires.php

find $TMP_DECRYPT_DIR -type f -cmin +1 -delete

php $EXPIRE_CLEANUP_SCRIPT
