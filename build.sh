#!/bin/bash

# Build drupal + lightning from makefile
drush make --concurrency=5 build-lightning.make docroot -y
# Rebuild dependencies from the make file contained in the repo (not the one
# pulled from D.O)
drush make drupal-org.make.yml docroot/profiles/lightning --no-core -y
# Copy the install profile and Lightning-specific modules in the repo, since drupal.org
# (which is used by drush make) is probably not as current.
cp -f lightning.* docroot/profiles/lightning
cp -R -f modules/lightning_features docroot/profiles/lightning/modules

# Notify users that they can use the local settings script.
echo "Run ./build-local-settings.sh to create the necessary files for install."
