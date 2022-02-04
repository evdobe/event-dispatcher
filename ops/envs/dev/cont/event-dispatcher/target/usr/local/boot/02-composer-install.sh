#!/bin/bash

PARENT_PATH=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
if [ ! -f composer.json ]; then
    runuser -l hostuser -c "cp $PARENT_PATH/assets/composer.json composer.json"
    runuser -l hostuser -c "composer require php-di/php-di doctrine/annotations"
    runuser -l hostuser -c "composer require --dev phpunit/phpunit"
fi
runuser -l hostuser -c "composer install --ignore-platform-req=ext-rdkafka"