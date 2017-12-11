<?php

namespace Drupal\lightning_media_twitter\Plugin\media\Source;

use Drupal\lightning_media\InputMatchInterface;
use Drupal\lightning_media\ValidationConstraintMatchTrait;
use Drupal\media_entity_twitter\Plugin\media\Source\Twitter as BaseTwitter;

/**
 * Input-matching version of the Twitter media source.
 */
class Twitter extends BaseTwitter implements InputMatchInterface {

  use ValidationConstraintMatchTrait;

}
