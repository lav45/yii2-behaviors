FROM alpine:3.16

RUN apk update --no-cache
RUN apk upgrade --update-cache --available

ARG UID
ARG GID

RUN apk add shadow
RUN adduser -S -G www-data -u ${UID} -h /var/www -s /bin/sh www-data
RUN groupmod --non-unique --gid ${GID} www-data

# php
RUN apk add php8

# composer
RUN apk add git
RUN apk add php8-phar
RUN apk add php8-openssl openssl
RUN apk add php8-iconv
RUN wget https://getcomposer.org/installer -O - | php -- --with-openssl --install-dir=/usr/local/bin --filename=composer

# php extension
RUN apk add php8-ctype
RUN apk add php8-intl icu-data-full
RUN apk add php8-mbstring
RUN apk add php8-opcache
RUN apk add php8-pdo_sqlite sqlite
RUN apk add php8-dom
RUN apk add php8-tokenizer

# clean
RUN rm -rf /var/cache/apk/* /tmp/*