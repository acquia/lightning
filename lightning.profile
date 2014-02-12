<?php

/**
 * @file
 * Enables modules and site configuration for Lightning site installation.
 */

/**
 * Implements hook_permission().
 */
function lightning_permission() {
  return array(
    'ride the lightning' => array(
      'title' => t('Administer Lightning'),
      'description' => t('Perform administration tasks for Lightning.'),
    ),
  );
}
