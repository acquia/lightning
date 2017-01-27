<?php

namespace Drupal\lightning_workflow;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides data to Views (i.e., via hook_views_data()).
 */
class ViewsData {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ViewsData constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns all relevant data for Views.
   *
   * @return array
   *   The data exposed to Views, in the format expected by hook_views_data().
   */
  public function getAll() {
    $data = [];

    // Any revisionable, Views-aware content entity type with a status key is
    // eligible.
    // @TODO: Use the published key and EntityPublishedInterface in Drupal 8.3.
    $filter = function (EntityTypeInterface $entity_type) {
      return (
        $entity_type instanceof ContentEntityTypeInterface &&
        $entity_type->isRevisionable() &&
        $entity_type->hasHandlerClass('views_data')
      );
    };
    /** @var ContentEntityTypeInterface[] $entity_types */
    $entity_types = array_filter($this->entityTypeManager->getDefinitions(), $filter);

    foreach ($entity_types as $id => $entity_type) {
      $table = $this->entityTypeManager
        ->getHandler($id, 'views_data')
        ->getViewsTableForEntityType($entity_type);

      $data[$table]['forward_revision_exists'] = [
        'title' => t('Forward revision(s) exist'),
        'field' => [
          'id' => 'forward_revision_exists',
          'real field' => $entity_type->getKey('id'),
        ],
      ];
    }

    return $data;
  }

}
