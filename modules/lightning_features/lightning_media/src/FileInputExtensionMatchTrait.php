<?php

namespace Drupal\lightning_media;

use Drupal\file\FileInterface;
use Drupal\media_entity\MediaBundleInterface;

/**
 * Implements InputMatchInterface for media types that use a file field.
 */
trait FileInputExtensionMatchTrait {

  /**
   * Returns the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  private function entityTypeManager() {
    return @($this->entityTypeManager ?: \Drupal::entityTypeManager());
  }

  /**
   * Implements InputMatchInterface::appliesTo().
   */
  public function appliesTo($input, MediaBundleInterface $bundle) {
    if (is_numeric($input)) {
      $input = $this->entityTypeManager()->getStorage('file')->load($input);
    }

    if ($input instanceof FileInterface) {
      $configuration = $this->getConfiguration();

      /** @var \Drupal\field\FieldConfigInterface $field */
      $field = $this->entityTypeManager()
        ->getStorage('field_config')
        ->load('media.' . $bundle->id() . '.' . $configuration['source_field']);

      $extension = pathinfo($input->getFilename(), PATHINFO_EXTENSION);
      $extension = strtolower($extension);

      $extensions = preg_split('/,?\s+/', $field->getSetting('file_extensions'));

      return in_array($extension, $extensions);
    }
    else {
      return FALSE;
    }
  }

}
