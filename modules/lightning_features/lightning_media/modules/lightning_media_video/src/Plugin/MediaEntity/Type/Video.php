<?php

namespace Drupal\lightning_media_video\Plugin\MediaEntity\Type;

use Drupal\lightning_media\InputMatchInterface;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\video_embed_media\Plugin\MediaEntity\Type\VideoEmbedField;

/**
 * Input-matching version of the VideoEmbedField media type.
 */
class Video extends VideoEmbedField implements InputMatchInterface {

  /**
   * {@inheritdoc}
   */
  public function appliesTo($input, MediaBundleInterface $bundle) {
    return (boolean) $this->providerManager->loadProviderFromInput($input);
  }

}
