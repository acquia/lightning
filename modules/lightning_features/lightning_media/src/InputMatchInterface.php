<?php

namespace Drupal\lightning_media;

use Drupal\media_entity\MediaBundleInterface;

/**
 * An interface for media type plugins to tell if they can handle mixed input.
 */
interface InputMatchInterface {

  /**
   * Checks if this media type can handle a given input value.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\media_entity\MediaBundleInterface $bundle
   *   The media bundle that is using this plugin.
   *
   * @return bool
   *   TRUE if the input can be handled by this plugin, FALSE otherwise.
   */
  public function appliesTo($value, MediaBundleInterface $bundle);

}
