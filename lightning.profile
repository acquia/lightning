<?php

/**
 * @file
 * Defines the Lightning Profile install screen by modifying the install form.
 */

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
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
 */
function lightning_post_install_redirect(array &$install_state) {
  $redirect = \Drupal::service('lightning.extender')->getRedirect();
  if ($redirect) {
    install_goto($redirect);
  }
}

/**
 * Rebuilds the service container.
 */
function lightning_rebuild_container() {
  require_once \Drupal::root() . '/core/includes/utility.inc';
  $class_loader = \Drupal::service('class_loader');
  $request = \Drupal::request();
  drupal_rebuild($class_loader, $request);
}

/**
 * Implements template_preprocess_block().
 */
function lightning_preprocess_block(array &$variables) {
  $variables['attributes']['data-block-plugin-id'] = $variables['elements']['#plugin_id'];
}

/**
 * Reads a stored config file from a module's config/install directory.
 *
 * @param string $id
 *   The config ID.
 * @param string $module
 *   (optional) The module to search. Defaults to 'lightning' (not technically
 *   a module, but profiles are treated like modules by the install system).
 *
 * @return array
 *   The config data.
 */
function lightning_read_config($id, $module = 'lightning') {
  // Statically cache all FileStorage objects, keyed by module.
  static $storage = [];

  if (empty($storage[$module])) {
    $dir = \Drupal::service('module_handler')->getModule($module)->getPath();
    $storage[$module] = new FileStorage($dir . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY);
  }
  return $storage[$module]->read($id);
}
