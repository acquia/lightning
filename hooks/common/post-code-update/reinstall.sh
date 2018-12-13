#!/bin/sh
#
# Cloud Hook: Reinstall Lightning
#
# Run `drush site-install lightning` in the target environment.

which drush
drush --version

site="$1"
target_env="$2"

# Fresh install of Lightning.
/usr/local/bin/drush9 @$site.$target_env site-install lightning --account-pass=admin --yes
