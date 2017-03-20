<?php

namespace Drupal\lightning_core;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Helps query and configure various display settings.
 */
class DisplayHelper {

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * DisplayHelper constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(QueryFactory $query_factory, EntityFieldManagerInterface $entity_field_manager) {
    $this->queryFactory = $query_factory;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Returns the first available preferred view mode.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $bundle
   *   The bundle.
   * @param string[] $preferences
   *   The view mode IDs to check, in descending order of preference.
   *
   * @return string
   *   The first preferred view mode ID that has a view display associated with
   *   it. If there are none, falls back to the default view mode.
   */
  public function getPreferredMode($entity_type, $bundle, array $preferences) {
    $displays = $this->queryFactory
      ->get('entity_view_display')
      ->execute();

    foreach ($preferences as $view_mode) {
      if (in_array($entity_type . '.' . $bundle . '.' . $view_mode, $displays)) {
        return $view_mode;
      }
    }
    return 'default';
  }

  /**
   * Returns the components newly added to a display.
   *
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $display
   *   The display config entity.
   *
   * @return array
   *   The newly added components.
   */
  public function getNewComponents(EntityDisplayInterface $display) {
    if (isset($display->original)) {
      return array_diff_key($display->getComponents(), $display->original->getComponents());
    }
    else {
      return [];
    }
  }

  /**
   * Returns newly added field components, optionally filtered by a function.
   *
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $display
   *   The display config entity.
   * @param callable|NULL $filter
   *   (optional) The function on which to filter the fields, accepting the
   *   field storage definition as an argument.
   *
   * @return array
   *   The newly added components.
   */
  public function getNewFields(EntityDisplayInterface $display, callable $filter = NULL) {
    $fields = $this->entityFieldManager->getFieldStorageDefinitions(
      $display->getTargetEntityTypeId()
    );
    if ($filter) {
      $fields = array_filter($fields, $filter);
    }
    return array_intersect_key($this->getNewComponents($display), $fields);
  }

}
