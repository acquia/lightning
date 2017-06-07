#!/bin/sh
#
# Cloud Hook: Reinstall Lightning
#
# Run `drush site-install lightning` in the target environment.

site="$1"
target_env="$2"

# Create the config directories defined in the settings file by pipelines.
chmod -R +w /var/www/html/$site.$target_env/config/
mkdir -p /var/www/html/$site.$target_env/config/default

# Fresh install of Lightning.
drush @$site.$target_env site-install lightning --account-pass=admin --yes