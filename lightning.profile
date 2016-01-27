<?php
/**
 * @file
 * Enables defines the Lightning Profile install screen by modifying the install
 * form.
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
  $form['lightning'] = [
    '#type' => 'details',
    '#title' => t('Lightning Features'),
    '#weight' => -5,
    '#open' => TRUE,
  ];

  // Checkboxes to enable Lightning Features.
  $form['lightning']['extensions'] = [
    '#type' => 'checkboxes',
    '#title' => t('Enable Extensions'),
    '#description' => 'You can choose to disable some of Lightning\'s functionality above. However, it is not recommended.',
    '#options' => [
      'lightning_media' => 'Lightning Media',
      'lightning_layout' => 'Lightning Layout',
      'lightning_workflow' => 'Lightning Workflow',
    ],
  ];
  // All our extensions are checked by default.
  $form['lightning']['extensions']['#default_value'] = array_keys($form['lightning']['extensions']['#options']);

  // Detail container for the demo content checkboxes.
  //
  // @todo It would be nice if the checkboxes in here were disabled if the
  // parent feature wasn't checked. For now it's only handled on server-side
  // validation.
  $form['lightning']['democontent_container'] = [
    '#type' => 'details',
    '#title' => t('Lightning Demo Content'),
    '#description' => 'Optionally, generate some demo content for Lightning\'s functional areas. You can only enable demo content for a functionql area if its parent feature is also enabled. For example, you can only enable Lightning Media Demo Content if Lightning Media is checked above.',
    '#weight' => 2,
    '#open' => TRUE,
  ];

  $form['lightning']['democontent_container']['democontent'] = [
    '#type' => 'checkboxes',
    '#title' => t('Enable Demo Content'),
    '#options' => [
      'lightning_media_democontent' => 'Lightning Media Demo Content',
    ],
    '#default_value' => ['lightning_media_democontent'],
  ];

  // Additional validate and submit handlers for Lightning settings.
  $form['#validate'][] = 'lightning_extensions_validate';
  $form['#submit'][] = 'lightning_extensions_enable';
}

/**
 * Don't allow demo content to be installed for feature that isn't installed.
 */
function lightning_extensions_validate($form, &$form_state) {
  $requested_demos = array_filter($form_state->getValue('democontent'));
  $enabled_features = array_values(array_filter($form_state->getValue('extensions')));
  foreach ($requested_demos as $requested_demo) {
    $requested_demo_parent = substr($requested_demo, 0, -12);
    if (!in_array($requested_demo_parent, $enabled_features)) {
      $form_state->setErrorByName('[democontent_container]', 'You cannot enable demo content without the parent feature also enabled.');
    }
  }
}

/**
 * Enable requested Lightning extensions and demo content.
 */
function lightning_extensions_enable($form_id, &$form_state) {
  $features = array_filter($form_state->getValue('extensions'));
  $demos = array_filter($form_state->getValue('democontent'));
  $enable = array_merge($features, $demos);
  if (isset($enable)) {
    \Drupal::service('module_installer')->install($enable, TRUE);
  }
}

