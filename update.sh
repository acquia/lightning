#!/bin/bash

FIXTURE=$TRAVIS_BUILD_DIR/tests/fixtures/$1.php.gz

if [ -f $FIXTURE ]; then
    drush sql:drop --yes
    php core/scripts/db-tools.php import $FIXTURE

    drush php:script $TRAVIS_BUILD_DIR/tests/update.php

    # # Reinstall modules which were blown away by the database restore.
    orca fixture:enable-modules
fi

drush updatedb --yes
drush update:lightning --no-interaction --yes

# Reinstall from exported configuration to prove that it's coherent.
drush config:export --yes
drush site:install --yes --existing-config

# Disable the History module during testing, to prevent weird deadlock errors
# that don't actually affect anything.
drush pm-uninstall --yes history

orca fixture:backup --force
