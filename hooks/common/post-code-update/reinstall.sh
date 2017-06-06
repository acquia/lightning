#!/bin/sh
#
# Cloud Hook: Reinstall Lightning
#
# Run `drush site-install lightning` in the target environment.

site="$1"
target_env="$2"

mkdir -p $target_env/config/sync
drush @$site.$target_env site-install lightning --account-pass=admin --yes