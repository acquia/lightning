<?php

namespace Drupal\lightning;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for interactive update plugins.
 */
abstract class UpdateBase extends PluginBase implements UpdateInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The console output driver.
   *
   * @var \Symfony\Component\Console\Style\OutputStyle
   */
  protected $io;

  /**
   * UpdateBase constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Symfony\Component\Console\Style\OutputStyle $io
   *   The console output driver.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The string translation service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OutputStyle $io, TranslationInterface $translation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->io = $io;
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, OutputStyle $io = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $io,
      $container->get('string_translation')
    );
  }

}
