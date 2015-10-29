<?php
/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function lightning_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Add a value as example that one can choose an arbitrary site name.
  $form['site_information']['site_name']['#placeholder'] = t('Lightning Demo');

  // Add 'Lightning' fieldset and options.
  $form['lightning'] = array(
    '#type' => 'fieldset',
    '#title' => t('Lightning Features'),
    '#weight' => -5,
  );

  // Checkboxes to enable Lightning Features.
  $form['lightning']['extensions'] = array(
    '#type' => 'checkboxes',
    '#title' => 'Enable Extensions',
    '#description' => 'You can choose to disable some of Lightning\'s functionality above. However, it is not recommended.',
    '#options' => array(
      'lightning_media' => 'Lightning Media',
      'lightning_layout' => 'Lightning Layout'
    ),
    '#default_value' => array('lightning_media', 'lightning_layout'),
  );

  // Additional submit handlers for Lightning settings.
  $form['#submit'][] = 'lightning_extensions_enable';
}

/**
 * Enable requested Lightning extensions.
 */
function lightning_extensions_enable($form_id, &$form_state) {
  $values = array_filter($form_state->getValue('extensions'));
  if (isset($values)) {
    \Drupal::service('module_installer')->install($values, TRUE);
  }
}
