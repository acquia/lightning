#!/bin/bash
# @file
# Behat integration - Script step.

set -e $DRUPAL_TI_DEBUG

# Ensure we are in the right directory.
cd "$DRUPAL_TI_DRUPAL_DIR"

# Now go to the local behat tests, being within the module installation is
# needed for example for the drush runner.
cd "$DRUPAL_TI_BEHAT_DIR"

lightning_header Running tests

# Copy into place because it doesn't come with panopoly_test.
mv -f "$TRAVIS_BUILD_DIR"/behat.travis.yml.dist .

# If this isn't an upgrade, we test if any features are overridden.
if [[ "$UPGRADE" == none ]]
then
	DRUSH_ARGS="--root=$DRUPAL_TI_DRUPAL_DIR --uri=$DRUPAL_TI_WEBSERVER_URL:$DRUPAL_TI_WEBSERVER_PORT" "$TRAVIS_BUILD_DIR"/scripts/check-overridden.sh
fi

# This replaces environment vars from $DRUPAL_TI_BEHAT_YML into 'behat.yml'.
drupal_ti_replace_behat_vars

ARGS=( $DRUPAL_TI_BEHAT_ARGS )

# First, run all the tests in Firefox.
./bin/behat "${ARGS[@]}"

# Then run some Chrome-only tests.
./bin/behat -p chrome "${ARGS[@]}"
