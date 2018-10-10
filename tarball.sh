#!/bin/bash

DESTINATION=`pwd`
WORK_DIR=/tmp
ARCHIVE=lightning-8.x-$1
PROFILE_DIR=profiles/contrib/lightning

composer archive --file $ARCHIVE --dir $WORK_DIR
cd $WORK_DIR
curl -L -o drush https://github.com/drush-ops/drush/releases/download/8.1.16/drush.phar
chmod +x drush
./drush make $DESTINATION/drupal-org-core.make $ARCHIVE
./drush make --no-core $DESTINATION/drupal-org.make $ARCHIVE
cd $ARCHIVE
composer require j7mbo/twitter-api-php league/oauth2-server:~6.0 webflo/drupal-core-strict:~8.5.0 'phpdocumentor/reflection-docblock:^3.0||^4.0'
mkdir -p $PROFILE_DIR
tar -x -f ../$ARCHIVE.tar --directory $PROFILE_DIR
cd ..
tar --exclude='.DS_Store' --exclude='._*' -c -z -f $DESTINATION/$ARCHIVE.tar.gz $ARCHIVE
rm -r -f drush $ARCHIVE.tar $ARCHIVE
