#!/bin/bash 
sudo chmod 0777 /opt/laravel --recursive 
/usr/bin/composer config repositories.aelora/markdownblog path /opt/markdown-blog/ --working-dir=/opt/laravel
/usr/bin/composer require erusev/parsedown spatie/laravel-ray aelora/laravel-markdown-blog --working-dir=/opt/laravel
sudo service ssh start
export APP_ENV=dev
/opt/laravel/artisan serve --host 0.0.0.0 --port 80