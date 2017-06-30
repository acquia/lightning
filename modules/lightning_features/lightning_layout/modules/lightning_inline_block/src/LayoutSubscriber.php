<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\panels\PanelsEvents;
use Drupal\panels\PanelsVariantEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LayoutSubscriber implements EventSubscriberInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * LayoutSubscriber constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PanelsEvents::VARIANT_POST_SAVE => 'onVariantPostSave',
    ];
  }

  /**
   * Reacts when a Panels display variant is saved.
   *
   * @param \Drupal\panels\PanelsVariantEvent $event
   *   The event object.
   */
  public function onVariantPostSave(PanelsVariantEvent $event) {
    $variant = $event->getVariant();
    $configuration = $variant->getConfiguration();

    $blocks = array_filter($configuration['blocks'], function (array $block) {
      return $block['id'] == 'inline_entity';
    });

    $this->database
      ->update('inline_entity')
      ->fields([
        'storage_type' => $variant->getStorageType(),
        'storage_id' => $variant->getStorageId(),
        'temp_store_id' => $variant->getTempStoreId(),
      ])
      ->condition('block_id', array_keys($blocks), 'IN')
      ->execute();
  }

}
