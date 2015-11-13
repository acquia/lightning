<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Plugin\views\row\EntityEmbedDeriver.
 */

namespace Drupal\lightning_media\Plugin\views\row;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\views\Plugin\Derivative\ViewsEntityRow;
use Drupal\views\ViewsData;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityEmbedDeriver extends ViewsEntityRow {

  use StringTranslationTrait;

  /**
   * Constructs an EntityEmbedDeriver object.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\views\ViewsData $views_data
   *   The views data service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct($base_plugin_id, EntityManagerInterface $entity_manager, ViewsData $views_data, TranslationInterface $translator) {
    parent::__construct($base_plugin_id, $entity_manager, $views_data);
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity.manager'),
      $container->get('views.views_data'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $definitions = parent::getDerivativeDefinitions($base_plugin_definition);

    foreach ($definitions as $id => $definition) {
      $definitions[$id]['title'] = $this->t('@title (embeddable)', [
        '@title' => $definition['title'],
      ]);
    }
    return $definitions;
  }

}
