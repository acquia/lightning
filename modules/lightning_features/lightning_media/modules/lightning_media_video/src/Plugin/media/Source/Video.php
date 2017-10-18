<?php

namespace Drupal\lightning_media_video\Plugin\media\Source;

use Drupal\lightning_media\InputMatchInterface;
use Drupal\media\MediaTypeInterface;
use Drupal\video_embed_media\Plugin\media\Source\VideoEmbedField;

/**
 * Input-matching version of the VideoEmbedField media type.
 */
class Video extends VideoEmbedField implements InputMatchInterface {

  /**
   * {@inheritdoc}
   */
  public function appliesTo($value, MediaTypeInterface $media_type) {
    return is_string($value)
      ? (bool) $this->providerManager->loadProviderFromInput($value)
      : FALSE;
  }

}
