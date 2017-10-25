<?php

namespace Drupal\Tests\lightning_media\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\Tests\media\Functional\MediaFunctionalTestCreateMediaTypeTrait;

/**
 * Tests of API-level Lightning functionality related to media types.
 *
 * @group lightning
 * @group lightning_media
 */
class MediaTypeTest extends KernelTestBase {

  use MediaFunctionalTestCreateMediaTypeTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'file',
    'image',
    'lightning_core',
    'lightning_media',
    'media',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    Config::forModule('lightning_media')
      ->getEntity('field_storage_config', 'media.field_media_in_library')
      ->save();
  }

  /**
   * Tests that field_media_in_library is auto-cloned for new media bundles.
   */
  public function testCloneMediaInLibraryField() {
    $type = $this->createMediaType([], 'file')->id();

    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->container
      ->get('entity_type.manager')
      ->getStorage('media')
      ->create([
        'bundle' => $type,
      ]);

    $this->assertTrue($media->hasField('field_media_in_library'));

    // The field should be present in the form as a checkbox.
    $component = entity_get_form_display('media', $type, 'default')
      ->getComponent('field_media_in_library');

    $this->assertInternalType('array', $component);
  }

}
