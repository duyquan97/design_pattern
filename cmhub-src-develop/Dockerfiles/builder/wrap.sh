#!/usr/bin/env bash
umask 027
chmod 0600 /etc/chef/chef-deploy
/usr/bin/env ssh -o "StrictHostKeyChecking=no" -o "UserKnownHostsFile=/dev/null" -i "/etc/chef/chef-deploy" $1 $2