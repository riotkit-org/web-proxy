FROM php:7.2-fpm

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \   
    && php composer-setup.php \
    && mv composer.phar /usr/bin/composer \
    && rm composer-setup.php \
    && chmod +x /usr/bin/composer \
    && usermod -u 1000 www-data \
    && groupmod -g 1000 www-data \
    && apt-get update \
    && apt-get install -y cron supervisor rsyslog git nano unzip nginx procps \
    && docker-php-ext-enable opcache \
    && apt-get clean

ADD docker/etc/cron/www-data /etc/cron.d/www-data
ADD docker/etc/supervisor/conf.d/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD docker/etc/nginx/nginx.conf /etc/nginx/nginx.conf
ADD docker/etc/nginx/sites-enabled/default /etc/nginx/sites-enabled/default
ADD docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh && mkdir /entrypoint.d && mkdir /run/php/ -p && chown www-data:www-data /run/php
ADD docker/entrypoint.d /entrypoint.d
ADD . /var/www

VOLUME ["/var/www"]
EXPOSE 80
EXPOSE 9000

ENTRYPOINT /entrypoint.sh
