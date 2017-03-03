#!/bin/bash

echo " >> Creating var/cache directory if not exists and setting up correct permissions"
mkdir /var/www/var/cache -p
chown www-data:www-data /var/www/var -R
