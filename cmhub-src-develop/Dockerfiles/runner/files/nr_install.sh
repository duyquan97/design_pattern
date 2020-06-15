#!/usr/bin/env bash
nr_install() {
 cd /tmp
 curl -O https://opex-team.s3-eu-west-1.amazonaws.com/files/newrelic-php5-8.7.0.242-linux-musl.tar.gz
 tar xvfz newrelic-php5-8.7.0.242-linux-musl.tar.gz
 rm newrelic-php5-8.7.0.242-linux-musl.tar.gz
 cp newrelic-php5-8.7.0.242-linux-musl/agent/x64/newrelic-20180731.so  /usr/local/lib/php/extensions/no-debug-non-zts-20180731/
 cp newrelic-php5-8.7.0.242-linux-musl/daemon/newrelic-daemon.x64 /usr/bin/
 ln /usr/local/lib/php/extensions/no-debug-non-zts-20180731/newrelic-20180731.so  /usr/local/lib/php/extensions/no-debug-non-zts-20180731/newrelic.so

 mkdir -p /etc/newrelic
 mkdir -p /var/log/newrelic

cat << EOF > /usr/local/etc/php/conf.d/newrelic.ini
extension = "newrelic.so"
[newrelic]
newrelic.license = "37123137d53e1a7de31e14c72428f9cd7963018b"
newrelic.logfile = "/var/log/newrelic/php_agent.log"
newrelic.daemon.logfile = "/var/log/newrelic/newrelic-daemon.log"
EOF

cat <<EOF > /etc/newrelic/newrelic.cfg
pidfile=/var/run/newrelic-daemon.pid
logfile=/var/log/newrelic/newrelic-daemon.log
#loglevel=info
port="/tmp/.newrelic.sock"
#auditlog=/var/log/newrelic/audit.log
#utilization.detect_docker=true
#app_timeout=10m
EOF

}

nr_install

