#!/bin/sh
#
# Cloud Hook: Reinstall Lightning
#
# Run `drush site-install lightning` in the target environment. This script works as
# any Cloud hook.

site="$1"
target_env="$2"

drush @$site.$target_env updatedb --yes
