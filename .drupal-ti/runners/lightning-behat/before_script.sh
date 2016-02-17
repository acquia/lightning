#!/bin/bash
# Simple script to install drupal for travis-ci running.

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed and upgraded.
# Note: This runs our custom version of drupal_ti_install_drupal in
#       .drupal-ti/functions/lightning.sh.
drupal_ti_ensure_drupal

# Clear caches and run a web server.
drupal_ti_clear_caches
drupal_ti_run_server

# Start xvfb and selenium.
drupal_ti_ensure_xvfb
drupal_ti_ensure_webdriver
