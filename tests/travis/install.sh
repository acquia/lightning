#!/usr/bin/env bash

# NAME
#     install.sh - Install Travis CI dependencies
#
# SYNOPSIS
#     install.sh
#
# DESCRIPTION
#     Creates the test fixture.

cd "$(dirname "$0")"

# Reuse ORCA's own includes.
source ../../../orca/bin/travis/_includes.sh

# Handle the special case of scanning for deprecations in contrib dependencies.
# We need to ensure that the components are included as part of the SUT,
# but none of the other Acquia product modules are.
if [[ "$ORCA_JOB" == "DEPRECATED_CODE_SCAN_CONTRIB" ]]; then
  export ORCA_PACKAGES_CONFIG=../lightning/tests/packages.yml
  orca fixture:init -f --sut="$ORCA_SUT_NAME" --dev --no-site-install
  exit 0
fi

# When testing the SUT in isolation using dev package versions, treat the
# components as part of the SUT, to be installed in an isolated (SUT-only)
# fixture.
if [[ "$ORCA_JOB" == "ISOLATED_DEV" ]]; then
  export ORCA_PACKAGES_CONFIG=../lightning/tests/packages.yml
  orca fixture:init -f --sut="$ORCA_SUT_NAME" --dev --profile=lightning
else
  # Run ORCA's standard installation script.
  ../../../orca/bin/travis/install.sh
fi

# If there is no fixture, there's nothing else for us to do.
[[ -d "$ORCA_FIXTURE_DIR" ]] || exit 0

# Add testing dependencies.
composer -d"$ORCA_FIXTURE_DIR" require --dev weitzman/drupal-test-traits:dev-master

cd "$ORCA_FIXTURE_DIR/docroot"

# Back up the current state of the fixture so we can proceed from there
# after verifying that installing from config still works after our
# update path.
orca fixture:backup --force

# Loop through every database fixture and restore it, then run the update
# path, export config, and reinstall the site from config to prove that
# the update path keeps the config coherent.
for DB in $TRAVIS_BUILD_DIR/tests/fixtures/*.php.gz
do
  drush sql:drop --yes
  php core/scripts/db-tools.php import ${DB}
  drush php:script "$TRAVIS_BUILD_DIR/tests/update.php"
  drush updatedb --yes
  drush update:lightning --no-interaction --yes
  drush config:export --yes
  drush site:install --yes --existing-config
done

# Restore the fixture backup.
orca fixture:reset

# Do manual updates required for existing site tests to pass, but which
# do not have automatic scripts.
drush role:perm:add layout_manager 'configure any layout'
drush theme:enable claro

# Disable the History module during testing, to prevent weird deadlock errors
# that don't actually affect anything.
drush pm:uninstall --yes history
