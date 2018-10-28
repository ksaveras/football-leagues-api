FROM phpdockerio/php72-fpm:latest
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

RUN groupadd -r -g 1000 tsp
RUN useradd -r -u 1000 -g tsp tsp

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install git \
    php7.2-mysql \
    php-redis \
    php7.2-sqlite3 \
    php7.2-bcmath \
    php7.2-gd \
    php-igbinary \
    php7.2-intl \
    php7.2-mbstring \
    php7.2-tidy \
    php-yaml \
    php-xdebug \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*; rm /etc/php/7.2/cli/conf.d/20-xdebug.ini

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
