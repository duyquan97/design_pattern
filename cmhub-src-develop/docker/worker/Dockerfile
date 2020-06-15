FROM alpine:3.9

ARG SSH_KEY
ARG GIT_CONFIG

ENV SSH_KEY=$SSH_KEY
ENV GIT_CONFIG=$GIT_CONFIG

ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub
RUN apk --update add ca-certificates \
    && echo "@cast https://dl.bintray.com/php-alpine/v3.9/php-7.3" >> /etc/apk/repositories \
    && apk add -U \
    # Packages
    tini \
    php7@cast \
    php7-dev@cast \
    php7-common@cast \
    php7-apcu@cast \
    php7-gd@cast \
    php7-xmlreader@cast \
    php7-bcmath@cast \
    php7-ctype@cast \
    php7-curl@cast \
    php7-exif@cast \
    php7-iconv@cast \
    php7-intl@cast \
    php7-json@cast \
    php7-mbstring@cast \
    php7-opcache@cast \
    php7-openssl@cast \
    php7-pcntl@cast \
    php7-pdo@cast \
    php7-mysqlnd@cast \
    php7-pdo_mysql@cast \
    php7-pdo_pgsql@cast \
    php7-phar@cast \
    php7-posix@cast \
    php7-session@cast \
    php7-xml@cast \
    php7-xsl@cast \
    php7-zip@cast \
    php7-zlib@cast \
    php7-dom@cast \
    php7-redis@cast \
    php7-fpm@cast \
    php7-sodium@cast \
    php7-soap@cast \
    php7-curl@cast \
    wget \
    curl \
    openssh \
    git \
    supervisor \
	# Clean up
    && ln -s /usr/bin/php7 /usr/bin/php \
    && rm -rf /var/cache/apk/*


RUN rm -rf /var/cache/apk/* && rm -rf /tmp/* && \
    curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

ADD php.ini /etc/php7/conf.d/
ADD php.ini /etc/php7/cli/conf.d/
ADD php-fpm.conf /etc/php7/php-fpm.d/

# Make ssh dir
RUN mkdir /root/.ssh/

# Create id_rsa from string arg, and set permissions

RUN echo "$SSH_KEY" > /root/.ssh/id_rsa
RUN chmod 600 /root/.ssh/id_rsa
RUN touch /root/.ssh/known_hosts
RUN ssh-keyscan gitlab.production.smartbox.com >> /root/.ssh/known_hosts

RUN touch /root/.gitconfig
RUN echo "$GIT_CONFIG" > /root/.gitconfig

RUN mkdir -p /etc/supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY availability-worker.conf /etc/supervisor/conf.d/availability-worker.conf
COPY transaction-worker.conf /etc/supervisor/conf.d/transaction-worker.conf
COPY rate-worker.conf /etc/supervisor/conf.d/rate-worker.conf
COPY booking-worker.conf /etc/supervisor/conf.d/booking-worker.conf
RUN mkdir /etc/supervisor/logs
RUN mkdir -p /var/log/supervisor

WORKDIR /var/www/cmhub

RUN adduser -D -g 'www' www \
    && chown -R www:www /var/www

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
