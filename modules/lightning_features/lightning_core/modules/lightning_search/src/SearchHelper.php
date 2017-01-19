<?php

namespace Drupal\lightning_search;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Helps with configuring Search API indices.
 */
class SearchHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Search API index storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $indexStorage;

  /**
   * The data source plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $dataSources;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * The current search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * All entity view display IDs.
   *
   * @var string[]
   */
  protected $displays = [];

  /**
   * SearchHelper constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param PluginManagerInterface $data_sources
   *   The data source plugin manager.
   * @param QueryFactory $query_factory
   *   The entity query factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PluginManagerInterface $data_sources, QueryFactory $query_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dataSources = $data_sources;
    $this->entityQueryFactory = $query_factory;

    $this->indexStorage = $this->entityTypeManager
      ->getStorage('search_api_index');

    $this->displays = $this->entityQueryFactory
      ->get('entity_view_display')
      ->execute();
  }

  /**
   * Switches the current index.
   *
   * @param string $index_id
   *   The ID of the index to configure.
   *
   * @return $this
   */
  public function configure($index_id) {
    $this->index = $this->indexStorage->load($index_id);
    return $this;
  }

  /**
   * Checks if an entity type is indexed by the current index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return bool
   *   TRUE if the entity type is indexed, FALSE otherwise.
   */
  public function isEnabled($entity_type) {
    return in_array('entity:' . $entity_type, $this->index->getDatasourceIds());
  }

  /**
   * Enables an entity type in the current index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return $this
   */
  public function enable($entity_type) {
    if ($this->isEnabled($entity_type) == FALSE) {
      /** @var \Drupal\search_api\Datasource\DatasourceInterface $plugin */
      $plugin = $this->dataSources->createInstance('entity:' . $entity_type);
      $this->index->addDatasource($plugin);

      $this
        ->indexRendered($entity_type)
        ->indexAggregatedKey($entity_type, 'label');
    }
    return $this;
  }

  /**
   * Commits all changes to the current index.
   */
  public function commit() {
    $this->index->save();
  }

  /**
   * Disables an entity type in the current index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return $this
   */
  public function disable($entity_type) {
    if ($this->isEnabled($entity_type)) {
      $this->index->removeDatasource('entity:' . $entity_type);

      $this
        ->unindexRendered($entity_type)
        ->unindexAggregatedKey($entity_type, 'label');
    }
    return $this;
  }

  /**
   * Adds a full rendering of an entity type to the index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return $this
   */
  protected function indexRendered($entity_type) {
    $field = $this->index->getField('rendered');

    if ($field) {
      $configuration = $field->getConfiguration();

      foreach ($this->getIndexViewModes($entity_type) as $bundle => $view_mode) {
        $configuration['view_mode']['entity:' . $entity_type][$bundle] = $view_mode;
      }
      $field->setConfiguration($configuration);
    }
    return $this;
  }

  /**
   * Determine the view modes to use for indexing all bundles of an entity type.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return string[]
   *   The view mode IDs, keyed by bundle.
   */
  protected function getIndexViewModes($entity_type) {
    // Prefer the search_index view mode, then full for content.
    $preferences = ['search_index'];
    if ($entity_type == 'node') {
      $preferences[] = 'full';
    }

    $bundle_type = $this->entityTypeManager
      ->getDefinition($entity_type)
      ->getBundleEntityType();

    $bundles = $bundle_type
      ? $this->entityQueryFactory->get($bundle_type)->execute()
      : [$entity_type];

    return array_map(
      function ($bundle) use ($entity_type, $preferences) {
        return $this->getIndexViewMode($entity_type, $bundle, $preferences);
      },
      array_combine($bundles, $bundles)
    );
  }

  /**
   * Determines the view mode to use for indexing a bundle of an entity type.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $bundle
   *   The bundle.
   * @param array $preferences
   *   The view modes to check for, in descending order of preference.
   *
   * @return string
   *   The view mode ID. Defaults to 'default' if none of the preferences exist.
   */
  protected function getIndexViewMode($entity_type, $bundle, array $preferences = ['search_index']) {
    foreach ($preferences as $view_mode) {
      if (in_array($entity_type . '.' . $bundle . '.' . $view_mode, $this->displays)) {
        return $view_mode;
      }
    }
    return 'default';
  }

  /**
   * Removes the full rendering of an entity type from the index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return $this
   */
  protected function unindexRendered($entity_type) {
    $field = $this->index->getField('rendered');

    if ($field) {
      $configuration = $field->getConfiguration();
      unset($configuration['view_mode']['entity:' . $entity_type]);
      $field->setConfiguration($configuration);
    }
    return $this;
  }

  /**
   * Returns the property path for an entity key.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $which
   *   The entity key.
   *
   * @return string
   *   The entity key's property path.
   */
  protected function getKeyProperty($entity_type, $which) {
    $key = $this->entityTypeManager
      ->getDefinition($entity_type)
      ->getKey($which);

    if ($entity_type == 'user' && $which == 'label') {
      return 'entity:' . $entity_type . '/name';
    }
    elseif ($key) {
      return 'entity:' . $entity_type . '/' . $key;
    }
  }

  /**
   * Adds an aggregated entity key to the index.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $key
   *   The entity key to add.
   *
   * @return $this
   */
  protected function indexAggregatedKey($entity_type, $key) {
    $field = $this->index->getField($key);
    $property = $this->getKeyProperty($entity_type, $key);

    if ($field && $property) {
      $configuration = $field->getConfiguration();
      $configuration['fields'][] = $property;
      $field->setConfiguration($configuration);
    }
    return $this;
  }

  /**
   * Removes an aggregated entity key from the index.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $key
   *   The entity key to remove.
   *
   * @return $this
   */
  protected function unindexAggregatedKey($entity_type, $key) {
    $field = $this->index->getField($key);
    $property = $this->getKeyProperty($entity_type, $key);

    if ($field && $property) {
      $configuration = $field->getConfiguration();
      $configuration['fields'] = array_diff($configuration['fields'], [$property]);
      $field->setConfiguration($configuration);
    }
    return $this;
  }

}
