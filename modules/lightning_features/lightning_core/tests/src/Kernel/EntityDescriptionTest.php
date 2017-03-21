<?php

namespace Drupal\Tests\lightning_core\Kernel;

use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning
 * @group lightning_core
 */
class EntityDescriptionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['lightning_core', 'user'];

  /**
   * Data provider for ::testEntityDescription().
   *
   * @return array
   *   The test data.
   */
  public function provider() {
    return [
      ['entity_form_mode'],
      ['entity_view_mode'],
      ['user_role'],
    ];
  }

  /**
   * Tests entity description functionality for an entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param array $values
   *   (optional) Values with which to create the entity.
   *
   * @dataProvider provider
   */
  public function testEntityDescription($entity_type_id, array $values = []) {
    $entity = $this->container
      ->get('entity_type.manager')
      ->getStorage($entity_type_id)
      ->create($values);

    $this->assertInstanceOf(EntityDescriptionInterface::class, $entity);
    /** @var \Drupal\Core\Entity\EntityDescriptionInterface $entity */
    $this->assertEmpty($entity->getDescription());
    $description = $this->randomString(32);
    $entity->setDescription($description);
    $this->assertEquals($description, $entity->getDescription());
  }

}
