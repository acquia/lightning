<?php

namespace Drupal\lightning_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A block that can display any entity.
 *
 * @Block(
 *   id = "entity_view",
 *   admin_label = @Translation("Entity View")
 * )
 */
class EntityViewBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge(parent::defaultConfiguration(), [
      'entity_type' => NULL,
      'entity_id' => NULL,
      'view_mode' => 'default',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search for the entity to display...'),
      '#attached' => [
        'library' => [
          'lightning_core/entity_search',
        ],
      ],
      '#id' => 'search',
    ];
    $form['entity_type'] = [
      '#type' => 'hidden',
      '#default_value' => $this->configuration['entity_type'],
    ];
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#default_value' => $this->configuration['entity_id'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
    $this->configuration['entity_id'] = $form_state->getValue('entity_id');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
