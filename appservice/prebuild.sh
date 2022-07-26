#!/bin/bash

PHP_VERSION=8.0.17
PHP_COMPOSER_VERSION=2.0.8

cd /tmp
curl -O https://oryx-cdn.microsoft.io/php/php-buster-${PHP_VERSION}.tar.gz
curl -O https://oryx-cdn.microsoft.io/php-composer/php-composer-buster-${PHP_COMPOSER_VERSION}.tar.gz

mkdir -p /tmp/oryx/platforms/php/${PHP_VERSION}
mkdir -p /tmp/oryx/platforms/php-composer/${PHP_COMPOSER_VERSION}

tar -xzf /tmp/php-buster-${PHP_VERSION}.tar.gz -C /tmp/oryx/platforms/php/${PHP_VERSION}
tar -xzf /tmp/php-composer-buster-${PHP_COMPOSER_VERSION}.tar.gz -C /tmp/oryx/platforms/php-composer/${PHP_COMPOSER_VERSION}