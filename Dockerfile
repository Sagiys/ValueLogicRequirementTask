FROM ubuntu:21.10

WORKDIR /app

RUN apt-get update
RUN apt-get install -y php
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
RUN update-alternatives --set php /usr/bin/php8.0

COPY . .

RUN composer install