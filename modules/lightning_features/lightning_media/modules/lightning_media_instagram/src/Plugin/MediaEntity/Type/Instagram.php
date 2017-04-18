<?php

namespace Drupal\lightning_media_instagram\Plugin\MediaEntity\Type;

use Drupal\lightning_media\InputMatchInterface;
use Drupal\lightning_media\SourceFieldInterface;
use Drupal\lightning_media\SourceFieldPluginTrait;
use Drupal\lightning_media\ValidationConstraintMatchTrait;
use Drupal\media_entity_instagram\Plugin\MediaEntity\Type\Instagram as BaseInstagram;

/**
 * Input-matching version of the Instagram media type.
 */
class Instagram extends BaseInstagram implements InputMatchInterface, SourceFieldInterface {

  use ValidationConstraintMatchTrait;
  use SourceFieldPluginTrait;

}
