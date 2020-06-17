#!/bin/bash

ARCHIVE=lightning-8.x-$1
PROFILE_DIR=profiles/contrib/lightning
YAML_CLI=`command -v yaml-cli`

# Ensure yaml-cli is installed, since we need it to set version numbers
# in the info files.
if [[ ! $YAML_CLI ]]; then
  echo "Cannot set version in info files because yaml-cli is not in your PATH."
  exit 1
fi

composer create-project --stability beta --no-install drupal/legacy-project:^8.8.8 $ARCHIVE
composer dump-autoload
composer configure-tarball $ARCHIVE

# Update version number in info files.
find . -name "*.info.yml" -exec $YAML_CLI update:value {} version 8.x-$1 \;

# Create an archive of the profile to be added to the tarball.
composer archive --file $ARCHIVE

# Remove modifications to info files.
git reset --hard

cd $ARCHIVE
composer config extra.enable-patching true
composer config minimum-stability dev
composer config prefer-stable true
composer remove --no-update composer/installers
composer require --no-update "ext-dom:*" cweagans/composer-patches oomphinc/composer-installers-extender
composer update

# Create the profile destination directory.
mkdir -p $PROFILE_DIR

# Extract the profile archive into it.
tar -x -f ../$ARCHIVE.tar --directory $PROFILE_DIR
cd ..

# Wrap it all up in a nice compressed tarball.
tar --exclude='.DS_Store' --exclude='._*' -c -z -f $ARCHIVE.tar.gz $ARCHIVE

# Clean up.
rm -r -f $ARCHIVE.tar $ARCHIVE
