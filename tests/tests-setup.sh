#!/bin/bash

curl -s https://getcomposer.org/installer | php
php composer.phar install
bin/behat --init

# Move all of the tests into the feature folder that behat created on init.
mv *.feature ./features

