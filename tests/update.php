<?php

/**
 * @file
 * Prepares a Lightning database fixture to be updated for testing.
 */

Drupal::configFactory()
  ->getEditable('core.extension')
  ->clear('module.lightning_dev')
  ->save();
