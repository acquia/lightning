<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Plugin\EmbedType\MediaLibrary.
 */

namespace Drupal\lightning_media\Plugin\EmbedType;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\embed\EmbedType\EmbedTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @EmbedType(
 *   id = "media_library",
 *   label = @Translation("Media Library")
 * )
 */
class MediaLibrary extends EmbedTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * MediaLibrary constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module_handler service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, TranslationInterface $translator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    $path = $this->moduleHandler->getModule('lightning_media')->getPath();
    return file_create_url($path . '/images/star.png');
  }

}
