# docker-registry.production.smartbox.com/cmhub/cmhub-builder:20200110
FROM php:7.3-fpm-alpine3.9

RUN sed -i '/xfs/ s/33/82/g' /etc/passwd; sed -i '/www-data/ s/82/33/g' /etc/passwd; \
sed -i '/xfs/ s/33/82/g' /etc/group; sed -i '/www-data/ s/82/33/g' /etc/group; \
chown 33.33 /var/www/html -R ;chown 33.33 /home/www-data -R

RUN wget https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub -O /etc/apk/keys/php-alpine.rsa.pub && \
apk --update add ca-certificates; \
echo "@php https://dl.bintray.com/php-alpine/v3.9/php-7.3" >> /etc/apk/repositories; \
apk add --update php7@php; \
ln -s /usr/bin/php7 /usr/bin/php; \
mkdir -p /run/nginx; \
apk add git \
bash \
openssh-client \
php7-curl@php \
php7-phar@php \
php7-opcache@php \
php7-json@php \
php7-openssl@php \
php7-mbstring@php \
php7-iconv@php \
php7-mysqlnd@php \
php7-mysqli@php \
php7-pdo@php \
php7-pdo_mysql@php \
php7-xml@php \
php7-soap@php \
php7-ctype@php \
php7-dom@php \
php-zlib@php \
php7-zip@php \
php7-intl@php \
nodejs \
nodejs-npm \
php7-phpdbg@php; \
cd /;wget https://getcomposer.org/installer -O /tmp/composer-setup.php; \
php /tmp/composer-setup.php;

RUN npm install newman -g
RUN wget -q -O /etc/apk/keys/sgerrand.rsa.pub https://alpine-pkgs.sgerrand.com/sgerrand.rsa.pub \
&& wget https://github.com/sgerrand/alpine-pkg-glibc/releases/download/2.29-r0/glibc-2.29-r0.apk \
&& apk add glibc-2.29-r0.apk
COPY chef-deploy /etc/chef/chef-deploy
COPY wrap.sh /etc/chef/wrap.sh
RUN docker-php-ext-install pdo_mysql
#install soap ext
RUN apk add libxml2-dev && docker-php-ext-install soap

COPY run.sh /run.sh

WORKDIR /app
CMD [ "/run.sh" ]
