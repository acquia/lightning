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

# This is a temporary workaround for a change in BLT 11.x which causes
# mikey179/vfsstream to be absent from the fixture, which breaks all
# kernel tests.
if [ -d $ORCA_FIXTURE_DIR ]; then
  cd $ORCA_FIXTURE_DIR
  composer require --dev mikey179/vfsstream weitzman/drupal-test-traits
fi
# End temporary workaround.

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
