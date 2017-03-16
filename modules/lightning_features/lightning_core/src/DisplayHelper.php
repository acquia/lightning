<?php

namespace Drupal\lightning_core;

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
   * DisplayHelper constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->queryFactory = $query_factory;
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

}
