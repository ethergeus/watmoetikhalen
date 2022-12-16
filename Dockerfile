FROM php:apache

RUN apt update && apt install -y ca-certificates openssl msmtp mailutils
RUN docker-php-ext-install mysqli

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo "sendmail_path=/usr/bin/msmtp -t" >> /usr/local/etc/php/conf.d/php-mail.ini

WORKDIR /var/www/html

COPY app .

RUN mkdir userdata/grades

VOLUME /var/www/html/userdata/grades
