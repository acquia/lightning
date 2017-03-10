<?php

namespace Drupal\lightning_core;

use Drupal\Core\Form\FormStateInterface;

/**
 * Adds description support to entity forms.
 */
trait EntityDescriptionFormTrait {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\EntityDescriptionInterface $entity */
    $entity = $this->getEntity();

    $form = parent::form($form, $form_state);

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#description' => $this->t('Additional relevant information about this @entity_type, such as where it is used and what it is for.', [
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
      ]),
      '#rows' => 2,
      '#default_value' => $entity->getDescription(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->getEntity()
      ->setDescription($form_state->getValue('description'));

    parent::save($form, $form_state);

    // The help text block is very likely to be render cached, so invalidate the
    // relevant cache tag.
    //
    // @see lightning_core_block_view_alter()
    // @see lightning_core_help().
    $this->cacheTagInvalidator()->invalidateTags(['block_view:help_block']);
  }

  /**
   * Returns the cache tag invalidator service.
   *
   * @return \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   *   The cache tag invalidator.
   */
  private function cacheTagInvalidator() {
    return @$this->cacheTagInvalidator ?: \Drupal::service('cache_tags.invalidator');
  }

}
