#!/usr/bin/env bash

SITE_DIR=$(pwd)/docroot/sites/default
SETTINGS=$SITE_DIR/settings.php

DB_URL=${DB_URL:-sqlite://db.sqlite}

# Delete previous settings.
if [[ -f $SETTINGS ]]; then
    chmod +w $SITE_DIR $SETTINGS
    rm $SETTINGS
fi

# Install Drupal.
drush site:install lightning --yes --config ./drush.yml --account-pass admin --db-url $DB_URL

# Make settings writable.
chmod +w $SITE_DIR $SETTINGS

# Copy development settings into the site directory.
cp settings.local.php $SITE_DIR

# Add Acquia Cloud subscription info to settings.php.
echo "if (file_exists('/var/www/site-php')) {" >> $SETTINGS
echo "  require '/var/www/site-php/lightningnightly/lightningnightly-settings.inc';" >> $SETTINGS
echo "  \$settings['install_profile'] = 'standard';" >> $SETTINGS
echo "}" >> $SETTINGS
echo "else {" >> $SETTINGS
echo "  require __DIR__ . '/settings.local.php';" >> $SETTINGS
echo "}" >> $SETTINGS
echo "require __DIR__ . '/settings.local.php';" >> $SETTINGS

# Copy PHPUnit configuration into core directory.
cp -f phpunit.xml ./docroot/core
