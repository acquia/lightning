<?php

namespace Drupal\lightning_media_image\Plugin\MediaEntity\Type;

use Drupal\lightning_media\FileInputExtensionMatchTrait;
use Drupal\lightning_media\InputMatchInterface;
use Drupal\media_entity_image\Plugin\MediaEntity\Type\Image as BaseImage;

/**
 * Input-matching version of the Image media type.
 */
class Image extends BaseImage implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
