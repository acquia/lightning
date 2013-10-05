<?php

/**
 * @file
 * Enables modules and site configuration for Lightning site installation.
 */

/**
 * Implements hook_install_tasks_alter().
 */
function lightning_install_tasks_alter(&$tasks, $install_state) {
  global $install_state;

  // Skip profile selection step.
  $tasks['install_select_profile']['display'] = FALSE;

  // Skip language selection install step and default language to English.
  $tasks['install_select_locale']['display'] = FALSE;
  $tasks['install_select_locale']['run'] = INSTALL_TASK_SKIP;
  $install_state['parameters']['locale'] = 'en';
}

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
