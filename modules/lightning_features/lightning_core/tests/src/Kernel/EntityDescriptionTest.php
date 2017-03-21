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
   * @param string $form_operation
   *   (optional) The entity form operation that exposes the description field.
   *   Defaults to 'default'.
   *
   * @dataProvider provider
   */
  public function testEntityDescription($entity_type_id, array $values = [], $form_operation = 'default') {
    $entity = $this->container
      ->get('entity_type.manager')
      ->getStorage($entity_type_id)
      ->create($values);

    $this->assertInstanceOf(EntityDescriptionInterface::class, $entity);
    /** @var \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\EntityDescriptionInterface $entity */
    $description = $this->randomString(32);
    $this->assertEmpty($entity->getDescription());
    $entity->setDescription($description);
    $this->assertEquals($description, $entity->getDescription());

    // If the entity type has a form for the provided form operation, build the
    // form and assert that it has a description field with the correct default
    // value.
    if ($entity->getEntityType()->getFormClass($form_operation)) {
      $form = $this->container
        ->get('entity.form_builder')
        ->getForm($entity, $form_operation);

      $this->assertInternalType('array', $form['description']);
      $this->assertEquals($description, $form['description']['#default_value']);
    }
  }

}
