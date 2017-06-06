#!/bin/sh
#
# Cloud Hook: Reinstall Lightning
#
# Run `drush site-install lightning` in the target environment.

site="$1"
target_env="$2"

# Create the config sync directory defined in the settings file by pipelines.
mkdir -p /var/www/html/$site.$target_env/docroot/sites/default/files/config/sync

# Fresh install of Lightning.
drush @$site.$target_env site-install lightning --account-pass=admin --yes