<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Plugin\CKEditorPlugin\MediaLibrary.
 */

namespace Drupal\lightning_media\Plugin\CKEditorPlugin;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\editor\Entity\Editor;
use Drupal\embed\EmbedButtonInterface;
use Drupal\embed\EmbedCKEditorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the media_library plugin for CKEditor.
 *
 * @CKEditorPlugin(
 *   id = "media_library",
 *   label = @Translation("Media Library"),
 *   embed_type_id = "media_library"
 * )
 */
class MediaLibrary extends EmbedCKEditorPluginBase {

  /**
   * The module handler service.
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
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\Query\QueryInterface $embed_button_query
   *   An entity query object for embed buttons.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryInterface $embed_button_query, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $embed_button_query);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query')->get('embed_button'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return ['drupalentity'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'lightning_media/backbone.facetr',
      'lightning_media/media_library',
      'media_entity_twitter/integration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->moduleHandler->getModule('lightning_media')->getPath() . '/js/media_library.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'mediaLibrary' => [
        // This allows several buttons to hook into the media library, which
        // opens the possibility of specific ones for embedding video, audio,
        // tweets, booplesnoots, etc.
        'buttons' => $this->getButtons(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getButton(EmbedButtonInterface $embed_button) {
    return [
      'id' => $embed_button->id(),
      'name' => Html::escape($embed_button->label()),
      'label' => Html::escape($embed_button->label()),
      'image' => $embed_button->getIconUrl(),
    ];
  }

}
