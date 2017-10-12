<?php

namespace Drupal\lightning_media;

use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\media\MediaTypeInterface;

/**
 * Implements InputMatchInterface for media types that use an embed code or URL.
 */
trait ValidationConstraintMatchTrait {

  /**
   * Returns the typed data manager.
   *
   * @return \Drupal\Core\TypedData\TypedDataManagerInterface
   *   The typed data manager.
   */
  private function typedDataManager() {
    return @($this->typedDataManager ?: \Drupal::typedDataManager());
  }

  /**
   * Implements InputMatchInterface::appliesTo().
   */
  public function appliesTo($value, MediaTypeInterface $media_type) {
    $plugin_definition = $this->getPluginDefinition();

    $definition = $this->typedDataManager()
      ->createDataDefinition('string')
      ->addConstraint($plugin_definition['input_match']['constraint']);

    $data = StringData::createInstance($definition);
    $data->setValue($value);

    return $data->validate()->count() === 0;
  }

}
