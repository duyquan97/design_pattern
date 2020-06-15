#!/bin/sh -e
if [[ ! -z "$NR_APPNAME" ]]; then
    ln -s /etc/sv/newrelic /service/newrelic
    echo "newrelic.appname = \"$NR_APPNAME\"" >> /usr/local/etc/php/conf.d/newrelic.ini
    ln /usr/local/etc/php/conf.d/newrelic.ini /etc/php7/conf.d/
fi
if [[ ! -z "$SERVICES_RUN" ]];then
    rm -fr /service/*
    for i in $SERVICES_RUN;do
        ln -s /etc/sv/$i /service/$i
    done
fi

export > /etc/envvars
exec /sbin/runsvdir /service

