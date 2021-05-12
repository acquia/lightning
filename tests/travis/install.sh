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
if [[ "$ORCA_JOB" == "DEPRECATED_CODE_SCAN_W_CONTRIB" ]]; then
  export ORCA_PACKAGES_CONFIG=../lightning/tests/packages.yml
  orca fixture:init -f --sut="$ORCA_SUT_NAME" --dev --no-site-install
  exit 0
fi

# When testing the SUT in isolation using dev package versions, treat the
# components as part of the SUT, to be installed in an isolated (SUT-only)
# fixture.
if [[ "$ORCA_JOB" == "ISOLATED_TEST_ON_CURRENT_DEV" ]]; then
  export ORCA_PACKAGES_CONFIG=../lightning/tests/packages.yml
  orca fixture:init -f --sut="$ORCA_SUT_NAME" --dev --profile=lightning
else
  composer require "drupal/core:^9.1.7" --no-update
  # Run ORCA's standard installation script.
  ../../../orca/bin/travis/install.sh
fi

# If there is no fixture, there's nothing else for us to do.
[[ -d "$ORCA_FIXTURE_DIR" ]] || exit 0

# Add testing dependencies.
composer -d"$ORCA_FIXTURE_DIR" require --dev weitzman/drupal-test-traits:dev-master phpspec/prophecy-phpunit:^2
