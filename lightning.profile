<?php

/**
 * @file
 * Enables modules and site configuration for Lightning site installation.
 */

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 */
function lightning_form_install_configure_form_alter(&$form, $form_state) {

  // Remove any non-error messages set by enabled modules.
  $messages = array('completed', 'status', 'warning');
  foreach ($messages as $message) {
    drupal_get_messages($message, TRUE);
  }

  // Add 'Lightning' fieldset and options.
  $form['lightning'] = array(
    '#type' => 'fieldset',
    '#title' => t('Lightning'),
    '#weight' => -5,
    '#collapsible' => FALSE,
    '#tree' => FALSE,
  );

  // Checkbox to enable Lightning options.
  $form['lightning']['extensions'] = array(
    '#type' => 'checkboxes',
    '#title' => 'Enable Extensions',
    '#description' => 'Optionally install extra features',
    '#options' => array('lightning_demo' => 'Demo Content', 'lightning_devel' => 'Developer Tools'),
    '#weight' => 0,
  );

  // Additional submit handlers for Lightning settings.
  $form['#submit'][] = 'lightning_extensions_enable';
}

/**
 * Enable requested Lightning extensions.
 */
function lightning_extensions_enable($form_id, &$form_state) {
  $values = $form_state['values'];
  if (isset($values['extensions'])) {
    foreach ($values['extensions'] as $module) {
      module_enable(array($module), TRUE);
    }
  }
}

