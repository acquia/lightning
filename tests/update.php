<?php

/**
 * @file
 * Prepares a code base to update to Drupal 9.
 */

Drupal::configFactory()
  ->getEditable('core.extension')
  ->clear('module.libraries')
  ->clear('module.openapi_redoc')
  ->save();

Drupal::configFactory()
  ->getEditable('libraries.settings')
  ->delete();

Drupal::keyValue('system.schema')->deleteMultiple([
  'libraries',
  'openapi_redoc',
]);
