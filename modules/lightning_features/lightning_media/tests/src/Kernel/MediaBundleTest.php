<?php

namespace Drupal\Tests\lightning_media\Kernel;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * Tests of API-level Lightning functionality related to media bundles.
 *
 * @group lightning_media
 */
class MediaBundleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'image',
    'lightning_media',
    'media_entity',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'field_media_in_library',
      'entity_type' => 'media',
      'type' => 'boolean',
    ])
      ->setCardinality(1)
      ->save();
  }

  /**
   * Tests that field_media_in_library is auto-cloned for new media bundles.
   */
  public function testCloneMediaInLibraryField() {
    MediaBundle::create([
      'id' => 'foo',
      'label' => $this->getRandomGenerator()->string(),
    ])->save();

    $field = FieldConfig::load('media.foo.field_media_in_library');
    $this->assertInstanceOf(FieldConfigInterface::class, $field);

    // The form display should be created if it doesn't already exist.
    $form_display = EntityFormDisplay::load('media.foo.default');
    $this->assertInstanceOf(EntityFormDisplayInterface::class, $form_display);

    // The field should be present in the form as a checkbox.
    $component = $form_display->getComponent('field_media_in_library');
    $this->assertInternalType('array', $component);
    $this->assertEquals('boolean_checkbox', $component['type']);
    $this->assertTrue($component['settings']['display_label']);
  }

}
