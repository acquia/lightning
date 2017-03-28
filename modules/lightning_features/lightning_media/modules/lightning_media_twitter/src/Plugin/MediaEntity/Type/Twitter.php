<?php

namespace Drupal\lightning_media_twitter\Plugin\MediaEntity\Type;

use Drupal\lightning_media\InputMatchInterface;
use Drupal\lightning_media\SourceFieldInterface;
use Drupal\lightning_media\SourceFieldPluginTrait;
use Drupal\lightning_media\ValidationConstraintMatchTrait;
use Drupal\media_entity_twitter\Plugin\MediaEntity\Type\Twitter as BaseTwitter;

/**
 * Input-matching version of the Twitter media type.
 */
class Twitter extends BaseTwitter implements InputMatchInterface, SourceFieldInterface {

  use ValidationConstraintMatchTrait;
  use SourceFieldPluginTrait;

}
