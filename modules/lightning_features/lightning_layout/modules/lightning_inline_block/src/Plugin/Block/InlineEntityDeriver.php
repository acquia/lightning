<?php

namespace Drupal\lightning_inline_block\Plugin\Block;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineEntityDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * InlineEntityDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $translator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->setStringTranslation($translator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type) {
      $mask = $entity_type->get('mask');
      if (empty($mask)) {
        continue;
      }

      $bundle_type = $entity_type->getBundleEntityType();
      if (empty($bundle_type)) {
        continue;
      }

      foreach ($this->entityTypeManager->getStorage($bundle_type)->loadMultiple() as $bundle) {
        $derivative = $base_plugin_definition;

        $derivative['admin_label'] = $this->t('@bundle @entity_type', [
          '@bundle' => $bundle->label(),
          '@entity_type' => $entity_type->getSingularLabel(),
        ]);

        $id = $entity_type->id() . ':' . $bundle->id();
        $this->derivatives[$id] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
