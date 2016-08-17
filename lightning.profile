<?php

/**
 * @file
 * Defines the Lightning Profile install screen by modifying the install form.
 */

use Drupal\lightning\Form\ExtensionSelectForm;

/**
 * Implements hook_install_tasks().
 */
function lightning_install_tasks() {
  return array(
    'lightning_select_extensions' => array(
      'display_name' => t('Choose extensions'),
      'display' => TRUE,
      'type' => 'form',
      'function' => ExtensionSelectForm::class,
    ),
    'lightning_install_extensions' => array(
      'display_name' => t('Install extensions'),
      'display' => TRUE,
      'type' => 'batch',
    ),
  );
}

/**
 * Implements hook_install_tasks_alter().
 */
function lightning_install_tasks_alter(array &$tasks, array $install_state) {
  $tasks['install_finished']['function'] = 'lightning_post_install_redirect';
}

/**
 * Install task callback; prepares a batch job to install Lightning extensions.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   The batch job definition.
 */
function lightning_install_extensions(array &$install_state) {
  $batch = array();
  foreach ($install_state['lightning']['modules'] as $module) {
    $batch['operations'][] = ['lightning_install_module', (array) $module];
  }
  return $batch;
}

/**
 * Batch API callback. Installs a module.
 *
 * @param string|array $module
 *   The name(s) of the module(s) to install.
 */
function lightning_install_module($module) {
  \Drupal::service('module_installer')->install((array) $module);
}

/**
 * Redirects the user to a particular URL after installation.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   A renderable array with a success message and a redirect header, if the
 *   extender is configured with one.
 */
function lightning_post_install_redirect(array &$install_state) {
  $redirect = \Drupal::service('lightning.extender')->getRedirect();

  $output = [
    '#title' => t('Ready to rock'),
    'info' => [
      '#markup' => t('Congratulations, you installed Lightning! If you are not redirected in 5 seconds, <a href="@url">click here</a> to proceed to your site.', [
        '@url' => $redirect,
      ]),
    ],
    '#attached' => [
      'http_header' => [
        ['Cache-Control', 'no-cache'],
      ],
    ],
  ];

  // The installer doesn't make it easy (possible?) to return a redirect
  // response, so set a redirection META tag in the output.
  $meta_redirect = [
    '#tag' => 'meta',
    '#attributes' => [
      'http-equiv' => 'refresh',
      'content' => '0;url=' . $redirect,
    ],
  ];
  $output['#attached']['html_head'][] = [$meta_redirect, 'meta_redirect'];

  return $output;
}
