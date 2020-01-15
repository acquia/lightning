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
composer -d"$ORCA_FIXTURE_DIR" require --dev weitzman/drupal-test-traits

# Exit early if no DB fixture is specified.
[[ "$DB_FIXTURE" ]] || exit 0

cd "$ORCA_FIXTURE_DIR/docroot"

DB="$TRAVIS_BUILD_DIR/tests/fixtures/$DB_FIXTURE.php.gz"

php core/scripts/db-tools.php import ${DB}

drush updatedb --yes
drush update:lightning --no-interaction --yes

# Do manual updates required for existing site tests to pass, but which
# do not have automatic scripts.
drush role:perm:add layout_manager 'configure any layout'
drush theme:enable claro

orca fixture:enable-extensions

# Reinstall from exported configuration to prove that it's coherent.
drush config:export --yes
drush site:install --yes --existing-config

# Disable the History module during testing, to prevent weird deadlock errors
# that don't actually affect anything.
drush pm:uninstall --yes history

# Set the fixture state to reset to between tests.
orca fixture:backup --force
