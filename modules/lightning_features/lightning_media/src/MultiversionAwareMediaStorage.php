<?php

namespace Drupal\lightning_media;

use Drupal\media_entity\MediaStorage;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;

/**
 * Media entity storage handler with Multiversion support.
 */
class MultiversionAwareMediaStorage extends MediaStorage {

  use ContentEntityStorageTrait;

}
