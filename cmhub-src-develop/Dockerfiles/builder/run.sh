#!/usr/bin/env bash
cd /app && GIT_SSH=/etc/chef/wrap.sh /composer.phar install -n --no-progress --optimize-autoloader
