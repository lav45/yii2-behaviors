FROM alpine:3.16

RUN apk update --no-cache
RUN apk upgrade --update-cache --available

ARG UID
ARG GID

RUN apk add shadow
RUN adduser -S -G www-data -u ${UID} -h /var/www -s /bin/sh www-data
RUN groupmod --non-unique --gid ${GID} www-data

# php
RUN apk add php81
RUN ln -s /usr/bin/php81 /usr/bin/php
RUN ln -s /etc/php81 /etc/php

# composer
RUN apk add git
RUN apk add php81-phar
RUN apk add php81-openssl openssl
RUN apk add php81-iconv
RUN wget https://getcomposer.org/installer -O - | php -- --with-openssl --install-dir=/usr/local/bin --filename=composer

# php extension
RUN apk add php81-ctype
RUN apk add php81-intl icu-data-full
RUN apk add php81-mbstring
RUN apk add php81-opcache
RUN apk add php81-pdo_sqlite sqlite
RUN apk add php81-dom
RUN apk add php81-tokenizer

# clean
RUN rm -rf /var/cache/apk/* /tmp/*