<?php

namespace Drupal\lightning_search;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Helps with configuring content search indices.
 */
class SearchHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The data source plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $dataSourceManager;

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * SearchHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $data_source_manager
   *   The Search API data source plugin manager.
   * @param string $index_id
   *   The ID of the search index to manipulate.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PluginManagerInterface $data_source_manager, $index_id) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dataSourceManager = $data_source_manager;

    $this->index = $this->entityTypeManager
      ->getStorage('search_api_index')
      ->load($index_id);
  }

  /**
   * Returns the definitions of the indexed entity types.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   The definitions of the indexed entity types, keyed by entity type ID.
   */
  public function getIndexedEntityTypes() {
    $entity_types = [];
    foreach ($this->index->getDatasources() as $data_source) {
      if ($data_source->getBaseId() == 'entity') {
        $id = $data_source->getDerivativeId();
        $entity_types[$id] = $this->entityTypeManager->getDefinition($id);
      }
    }
    return $entity_types;
  }

  /**
   * Adds an entity type to the search index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function enable($entity_type) {
    $label_key = $this->entityTypeManager->getDefinition($entity_type)->getKey('label');

    /** @var \Drupal\search_api\Datasource\DatasourceInterface $data_source */
    $data_source = $this->dataSourceManager->createInstance("entity:{$entity_type}");
    $this->index->addDatasource($data_source);

    $field = $this->index->getField('label');
    $configuration = $field->getConfiguration();
    $configuration['fields'][] = "entity:{$entity_type}/{$label_key}";
    $field->setConfiguration($configuration);

    $this->index->save();

    return $this;
  }

  /**
   * Removes an entity type from the search index.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function disable($entity_type) {
    $label_key = $this->entityTypeManager->getDefinition($entity_type)->getKey('label');

    $this->index->removeDatasource("entity:{$entity_type}");

    $field = $this->index->getField('label');
    $configuration = $field->getConfiguration();
    $configuration['fields'] = array_diff($configuration['fields'], ["entity:{$entity_type}/{$label_key}"]);
    $field->setConfiguration($configuration);

    $this->index->save();

    return $this;
  }

}
