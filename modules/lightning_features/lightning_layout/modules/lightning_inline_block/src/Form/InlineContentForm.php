<?php

namespace Drupal\lightning_inline_block\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class InlineContentForm extends BlockContentForm {

  /**
   * @return \Drupal\lightning_inline_block\InlineEntityInterface
   */
  public function getEntity() {
    return parent::getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->getEntity()
      ->getStorageContext()
      ->setConfiguration([
        'region' => $form_state->getValue('_region'),
      ]);

    parent::save($form, $form_state);

    if ($form_state->has('referrer')) {
      $redirect = $form_state->get('referrer');
      $redirect = Url::fromUri($redirect);

      $form_state->setRedirectUrl($redirect);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->getEntity();

    if ($entity->isNew()) {
      $form['_region'] = [
        '#type' => 'select',
        '#title' => $this->t('Region'),
        '#required' => TRUE,
        '#options' => $entity->getStorageContext()->getDisplay()->getRegionNames(),
      ];
    }
    return $form;
  }

}
