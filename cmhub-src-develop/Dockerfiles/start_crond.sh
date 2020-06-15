#!/bin/sh
unlink /service/nginx
unlink /service/php-fpm
ln -s /etc/sv/crond /service/cron

/sbin/runsvdir /service
