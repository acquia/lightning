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

# Handle the Contrib: Deprecated code scan special case.
if [[ "$ORCA_JOB" == "DEPRECATED_CODE_SCAN_CONTRIB" ]]; then
  export ORCA_PACKAGES_CONFIG=../lightning/tests/packages.yml
  orca fixture:init -f --sut="acquia/lightning" --dev --no-site-install
  exit 0
fi

# Make the Isolated dev job treat Lightning's components as part of the SUT
# so as to be installed in a SUT-only fixture.
if [[ "$ORCA_JOB" == "ISOLATED_DEV" ]]; then
  export ORCA_PACKAGES_CONFIG=../lightning/tests/packages.yml
  orca fixture:init -f --sut="acquia/lightning" --dev --profile=lightning
  exit 0
fi

# Run ORCA's standard install script.
../../../orca/bin/travis/install.sh

# Exit early in the absence of a fixture.
[[ -d "$ORCA_FIXTURE_DIR" ]] || exit 0

# Add test-only dependencies.
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
