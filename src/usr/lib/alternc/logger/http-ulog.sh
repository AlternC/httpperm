#!/bin/sh

if [ -f "/run/alternc/http-ulog.pid" -a -d "/proc/$(cat /run/alternc/http-ulog.pid)" ]
then
    exit
fi

echo $$ >/run/alternc/http-ulog.pid

cd /usr/lib/alternc/logger
tail -F /var/log/ulog/syslogemu.log |./http-ulog.php 
rm /run/alternc/http-ulog.pid

