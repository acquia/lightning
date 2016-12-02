<?php

namespace Drupal\lightning_media\Plugin\MediaBundleResolver;

use Drupal\file\FileInterface;
use Drupal\lightning_media\BundleResolverBase;

/**
 * Bundle resolver for uploaded files.
 *
 * @MediaBundleResolver(
 *   id = "file_upload",
 *   field_types = {"file", "image"}
 * )
 */
class FileUpload extends BundleResolverBase {

  /**
   * {@inheritdoc}
   */
  public function getBundle($input) {
    if ($input instanceof FileInterface) {
      foreach ($this->getPossibleBundles() as $bundle) {
        $field = $this->getSourceFieldForBundle($bundle);
        $extensions = preg_split('/,?\s+/', $field->getSetting('file_extensions'));

        $extension = pathinfo($input->getFilename(), PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        if (in_array($extension, $extensions)) {
          return $bundle;
        }
      }
    }
    return FALSE;
  }

}
