#!/bin/sh

set -eux

## Run templates with configuration.
/usr/local/bin/confd --onetime --backend env --confdir /etc/confd

## Start the nginx process.
/usr/sbin/nginx -g "daemon off;"
