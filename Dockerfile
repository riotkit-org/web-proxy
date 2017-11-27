FROM debian:stretch

RUN echo "deb http://deb.debian.org/debian stretch main contrib non-free" >> /etc/apt/sources.list
RUN apt-get update \
    && apt-get install -y nginx php7.0-cli php7.0-fpm php7.0-curl \ 
    php7.0-intl php7.0-json php7.0-mbstring php7.0-mysql php7.0-opcache \
    php7.0-sqlite3 php7.0-xml php7.0-readline php7.0-zip cron supervisor rsyslog git nano unzip \
    && apt-get clean

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \   
    && php composer-setup.php \
    && mv composer.phar /usr/bin/composer \
    && rm composer-setup.php \
    && chmod +x /usr/bin/composer

ADD etc/apt/preferences /etc/apt/preferences
ADD etc/supervisor/conf.d/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD etc/nginx/nginx.conf /etc/nginx/nginx.conf
ADD etc/nginx/sites-enabled/default etc/nginx/sites-enabled/default
ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh && mkdir /entrypoint.d && mkdir /run/php/ -p && chown www-data:www-data /run/php
ADD entrypoint.d /entrypoint.d

ADD . /var/www
RUN su www-data -c "cd /var/www && composer install"

VOLUME ["/var/www"]

ENTRYPOINT /entrypoint.sh
