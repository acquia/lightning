<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\ctools\Event\BlockVariantEvent;
use Drupal\ctools\Event\BlockVariantEvents;
use Drupal\lightning_inline_block\Controller\PanelsIPEController;
use Drupal\lightning_inline_block\Plugin\Block\InlineEntity;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Symfony\Component\Routing\RouteCollection;

class LayoutSubscriber extends RouteSubscriberBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LayoutSubscriber constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array_merge(
      parent::getSubscribedEvents(),
      [
        BlockVariantEvents::ADD_BLOCK => 'onAddBlock',
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $collection
      ->get('panels_ipe.block_content.form')
      ->setDefault('_controller', PanelsIPEController::class . '::getBlockContentForm');
  }

  public function onAddBlock(BlockVariantEvent $event) {
    $variant = $event->getVariant();
    $block = $event->getBlock();

    if ($variant instanceof PanelsDisplayVariant && $block instanceof InlineEntity) {
      $configuration = $block->getConfiguration();

      $contexts = $variant->getContexts();
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $contexts['@panelizer.entity_context:entity']->getContextValue();

      $this->database
        ->insert('inline_entity')
        ->fields([
          'uuid' => $block->getEntity()->uuid(),
          'block_id' => $configuration['uuid'],
          'entity_type' => $entity->getEntityTypeId(),
          'entity_id' => $entity->id(),
        ])
        ->execute();
    }
  }

}
