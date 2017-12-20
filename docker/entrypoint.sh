#!/bin/bash
for file_name in /entrypoint.d/*sh
do
    if [ -e "${file_name}" ]; then
        echo " >> entrypoint.d - executing $file_name"
        . "${file_name}"
    fi
done

su www-data -c "cd /var/www && composer install" &
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
