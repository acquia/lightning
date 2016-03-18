<?php

/**
 * @file
 * Enables defines the Lightning Profile install screen by modifying the install
 * form.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\user\Entity\Role;

/**
 * Implements hook_ENTITY_TYPE_insert().
 */
function lightning_node_type_insert(NodeTypeInterface $node_type) {
  Role::create([
    'id' => $node_type->id() . '_creator',
    'label' => t('@type Creator', [
      '@type' => $node_type->label(),
    ]),
    'permissions' => [
      'create ' . $node_type->id() . ' content',
      'edit own ' . $node_type->id() . ' content',
      'view ' . $node_type->id() . ' revisions',
      'view own unpublished content',
      'create url aliases',
    ],
  ])->save();

  Role::create([
    'id' => $node_type->id() . '_reviewer',
    'label' => t('@type Reviewer', [
      '@type' => $node_type->label(),
    ]),
    'permissions' => [
      'edit any ' . $node_type->id() . ' content',
      'delete any ' . $node_type->id() . ' content',
    ],
  ])->save();
}

/**
 * Implements hook_ENTITY_TYPE_delete().
 */
function lightning_node_type_delete(NodeTypeInterface $node_type) {
  $role = Role::load($node_type->id() . '_creator');
  if ($role) {
    $role->delete();
  }

  $role = Role::load($node_type->id() . '_reviewer');
  if ($role) {
    $role->delete();
  }
}

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function lightning_form_install_configure_form_alter(array &$form, FormStateInterface $form_state) {
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
    '#description' => t('You can choose to disable some of Lightning\'s functionality above. However, it is not recommended.'),
    '#options' => [
      'lightning_media' => 'Lightning Media',
      'lightning_layout' => 'Lightning Layout',
      'lightning_workflow' => 'Lightning Workflow',
    ],
  ];
  // All our extensions are checked by default.
  $form['lightning']['extensions']['#default_value'] = array_keys($form['lightning']['extensions']['#options']);

  $form['#submit'][] = 'lightning_extensions_enable';
}

/**
 * Enable requested Lightning extensions and demo content.
 */
function lightning_extensions_enable($form_id, FormStateInterface $form_state) {
  $features = array_filter($form_state->getValue('extensions'));
  if ($features) {
    if (in_array('lightning_media', $features)) {
      $features = array_merge($features, [
        'lightning_media_image',
        'lightning_media_instagram',
        'lightning_media_twitter',
        'lightning_media_video',
      ]);
    }
    \Drupal::service('module_installer')->install($features);
  }
}
