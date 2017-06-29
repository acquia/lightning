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
    /** @var \Drupal\lightning_inline_block\Entity\InlineBlockContent $entity */
    $entity = $this->getEntity();

    if ($block_id) {
      $configuration = $display->getBlock($block_id)->getConfiguration();
      $configuration['region'] = $form_state->getValue('_region');
    }

    $entity->storageInfo = [
      'storage_type' => $display->getStorageType(),
      'storage_id' => $display->getStorageId(),
      'temp_store_key' => $display->getTempStoreId(),
    ];
    $entity->storageInfo['block_id'] = $display->addBlock([
      'id' => 'inline_entity',
      'region' => $form_state->getValue('_region'),
      'entity' => serialize($entity),
    ]);
    parent::save($form, $form_state);

    $this->tempStore->set(
      $entity->storageInfo['temp_store_key'],
      $display->getConfiguration()
    );

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

    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $display */
    list ($display, $block_id) = $this->getEntity()->getStorageContext();

    $form['_region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#options' => $display->getRegionNames(),
    ];
    if ($block_id) {
      $configuration = $display->getBlock($block_id)->getConfiguration();
      $form['_region']['#default_value'] = $configuration['region'];
    }
    else {
      $form['_region']['#default_value'] = key($form['_region']['#options']);
    }

    return $form;
  }

}
