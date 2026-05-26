#!/bin/bash

# Agregar límite de tamaño a nginx
echo 'client_max_body_size 50M;' > /etc/nginx/conf.d/upload_size.conf

# Iniciar PHP-FPM y Nginx
php-fpm -D && nginx -g 'daemon off;'
