<?php

namespace Drupal\lightning;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * Container-aware plugin factory for instantiating interactive update plugins.
 */
class UpdateFactory extends ContainerFactory {

  /**
   * The console output driver.
   *
   * @var \Symfony\Component\Console\Style\OutputStyle
   */
  protected $io;

  /**
   * Sets the console output driver.
   *
   * @param \Symfony\Component\Console\Style\OutputStyle $io
   *   The console output driver.
   */
  public function setIO(OutputStyle $io) {
    $this->io = $io;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $class = static::getPluginClass($plugin_id, $plugin_definition, $this->interface);

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($class, ContainerFactoryPluginInterface::class)) {
      return $class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition, $this->io);
    }

    // Otherwise, create the plugin directly.
    return new $class($configuration, $plugin_id, $plugin_definition, $this->io);
  }

}
