#BUILD
FROM docker-registry.production.smartbox.com/cmhub/cmhub-builder:20191010 as builder

COPY . /app

WORKDIR /app

RUN cp /app/Dockerfiles/php.ini /usr/local/etc/php/.

RUN /run.sh

#CREATE CONTAINER
FROM docker-registry.production.smartbox.com/cmhub/cmhub-runner:20200110

ARG SSH_PRIVATE_KEY
ARG SSH_KNOWN_HOSTS

COPY Dockerfiles/runit/sv /etc/sv
COPY Dockerfiles/runit/runit /etc/runit
COPY Dockerfiles/start_crond.sh /
COPY Dockerfiles/start.sh /
RUN mkdir /service; ln -s /etc/sv/nginx /service/nginx;ln -s /etc/sv/php-fpm /service/php-fpm

USER www-data
COPY --chown=www-data:www-data --from=builder /app /app
USER root
RUN mkdir -p ~/.ssh;\
    chmod 700 ~/.ssh;\
    echo "$SSH_PRIVATE_KEY" >  ~/.ssh/id_rsa;\
    touch ~/.ssh/known_hosts;\
    echo "$SSH_KNOWN_HOSTS" > ~/.ssh/known_hosts;\
    chmod 644 ~/.ssh/known_hosts;\
    chmod 600 ~/.ssh/id_rsa;
RUN cd /app wget https://getcomposer.org/installer -O /tmp/composer-setup.php; \
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer; php /tmp/composer-setup.php;


RUN cp /app/Dockerfiles/php.ini /usr/local/etc/php/.

RUN setfacl -R -d -m u:www-data:rwx -m g:www-data:rwx /app/var/;mkdir -p /var/tmp/nginx;chown www-data.www-data /var/tmp/nginx -R;setfacl -R -d -m u:www-data:rwx -m g:www-data:rwx /var/tmp/nginx

WORKDIR /app
CMD [ "/start.sh" ]

