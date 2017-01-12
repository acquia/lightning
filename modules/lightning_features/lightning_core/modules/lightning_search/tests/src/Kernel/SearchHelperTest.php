<?php

namespace Drupal\Tests\lightning_search\Kernel;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\Item\Field;

/**
 * @covers \Drupal\lightning_search\SearchHelper
 * @group lightning_search
 */
class SearchHelperTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'search_api', 'taxonomy'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\search_api\IndexInterface $index */
    $index = $this->container->get('entity_type.manager')
      ->getStorage('search_api_index')
      ->create([
        'id' => 'content',
      ]);

    $field = (new Field($index, 'label'))
      ->setLabel('Label')
      ->setType('text')
      ->setPropertyPath('aggregated_field');

    $index->addField($field)->save();
  }

  /**
   * @covers ::enable
   */
  public function testEnable() {
    $this->container
      ->get('lightning.search_helper')
      ->enable('node')
      ->enable('taxonomy_term');

    /** @var \Drupal\search_api\IndexInterface $index */
    $index = $this->container
      ->get('entity_type.manager')
      ->getStorage('search_api_index')
      ->load('content');

    $data_sources = $index->getDatasourceIds();
    $this->assertContains('entity:node', $data_sources);
    $this->assertContains('entity:taxonomy_term', $data_sources);

    $field_configuration = $index->getField('label')->getConfiguration();
    $this->assertContains('entity:node/title', $field_configuration['fields']);
    $this->assertContains('entity:taxonomy_term/name', $field_configuration['fields']);
  }

  /**
   * @covers ::disable
   */
  public function testDisable() {
    $this->testEnable();

    $this->container
      ->get('lightning.search_helper')
      ->disable('node');

    /** @var \Drupal\search_api\IndexInterface $index */
    $index = $this->container
      ->get('entity_type.manager')
      ->getStorage('search_api_index')
      ->load('content');

    $data_sources = $index->getDatasourceIds();
    $this->assertNotContains('entity:node', $data_sources);
    $field_configuration = $index->getField('label')->getConfiguration();
    $this->assertNotContains('entity:node/title', $field_configuration['fields']);
  }

  /**
   * @covers ::getIndexedEntityTypes
   */
  public function testGetIndexedEntityTypes() {
    $this->testEnable();

    $entity_types = $this->container
      ->get('lightning.search_helper')
      ->getIndexedEntityTypes();

    $this->assertInstanceOf(EntityTypeInterface::class, $entity_types['node']);
    $this->assertInstanceOf(EntityTypeInterface::class, $entity_types['taxonomy_term']);
  }

}
