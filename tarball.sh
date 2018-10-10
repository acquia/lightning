#!/bin/bash

DESTINATION=`pwd`
WORK_DIR=/tmp
ARCHIVE=lightning-8.x-$1
PROFILE_DIR=profiles/contrib/lightning

# Archive the profile and copy it into the work directory.
composer archive --file $ARCHIVE --dir $WORK_DIR

# Create the complete make file, including Drupal core.
composer package > $WORK_DIR/tarball.make

cd $WORK_DIR

# Download Drush 8 (which has the make command) and make it executable.
curl -L -o drush https://github.com/drush-ops/drush/releases/download/8.1.16/drush.phar
chmod +x drush

# Build the code base.
./drush make tarball.make $ARCHIVE
cd $ARCHIVE

# Add Composer dependencies.
composer require j7mbo/twitter-api-php league/oauth2-server:~6.0 webflo/drupal-core-strict:~8.6.0 'phpdocumentor/reflection-docblock:^3.0||^4.0'

# Extract the archived profile into the built code base.
mkdir -p $PROFILE_DIR
tar -x -f ../$ARCHIVE.tar --directory $PROFILE_DIR
cd ..

# Archive the entire built code base.
tar --exclude='.DS_Store' --exclude='._*' -c -z -f $DESTINATION/$ARCHIVE.tar.gz $ARCHIVE

# Clean up.
rm -r -f drush $ARCHIVE.tar $ARCHIVE tarball.make
