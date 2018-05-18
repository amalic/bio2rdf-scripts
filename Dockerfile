FROM php:7.2

MAINTAINER Michel Dumontier <michel.dumontier@gmail.com>
MAINTAINER Alexander Malic <alexander.malic@gmail.com>

ARG APP_ENV=prod

WORKDIR /app

RUN apt-get update && \
  apt-get install -y git lftp zlib1g-dev && \
  docker-php-ext-install zip


RUN git clone https://github.com/micheldumontier/php-lib.git

COPY . .

ADD php.ini /usr/local/etc/php

RUN ls -alh && \
  php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer && \
  composer install

#ENTRYPOINT ["php", "runparser.php"]
ENTRYPOINT ["bash"]
