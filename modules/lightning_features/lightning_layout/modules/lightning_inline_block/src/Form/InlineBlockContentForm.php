<?php

namespace Drupal\lightning_inline_block\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class InlineBlockContentForm extends BlockContentForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->getEntity()->region = $form_state->getValue('_region');

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

    /** @var \Drupal\lightning_inline_block\Entity\InlineBlockContent $block */
    $block = $this->getEntity();

    $form['_region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#options' => $block->display->getRegionNames(),
      '#access' => $block->isNew(),
    ];
    $form['_region']['#default_value'] = key($form['_region']['#options']);

    return $form;
  }

}
