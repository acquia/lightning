<?php

namespace Drupal\lightning_media;

use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\media_entity\MediaBundleInterface;

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
  public function appliesTo($input, MediaBundleInterface $bundle) {
    $plugin_definition = $this->getPluginDefinition();

    $definition = $this->typedDataManager()
      ->createDataDefinition('string')
      ->addConstraint($plugin_definition['input_match']['constraint']);

    $value = StringData::createInstance($definition);
    $value->setValue($input);

    return $value->validate()->count() === 0;
  }

}
