<?php

namespace Drupal\lightning_core;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Helps with configuring the viewable_content search index.
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
   * The aggregated label field.
   *
   * @var \Drupal\search_api\Item\FieldInterface
   */
  protected $field;

  /**
   * SearchHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $data_source_manager
   *   The Search API data source plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PluginManagerInterface $data_source_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dataSourceManager = $data_source_manager;

    $this->index = $entity_type_manager
      ->getStorage('search_api_index')
      ->load('viewable_content');

    $this->field = $this->index->getField('label');
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

    $configuration = $this->field->getConfiguration();
    $configuration['fields'][] = "entity:{$entity_type}/{$label_key}";
    $this->field->setConfiguration($configuration);

    return $this->commit();
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

    $configuration = $this->field->getConfiguration();
    $configuration['fields'] = array_diff($configuration['fields'], ["entity:{$entity_type}/{$label_key}"]);
    $this->field->setConfiguration($configuration);

    return $this->commit();
  }

  /**
   * Saves any changes made to the search index.
   */
  protected function commit() {
    $this->index->addField($this->field)->save();
    return $this;
  }

}
