<?php

namespace Drupal\lightning_media\Update;

use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\media\MediaTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Media 2.2.4.
 *
 * @Update("2.2.4")
 */
final class Update224 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The media type entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaTypeStorage;

  /**
   * The media entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $mediaDefinition;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  public function __construct(EntityStorageInterface $media_type_storage, EntityTypeInterface $media_definition, TranslationInterface $translation, EntityTypeManager $entity_type_manager) {
    $this->mediaTypeStorage = $media_type_storage;
    $this->mediaDefinition = $media_definition;
    $this->setStringTranslation($translation);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('media_type'),
      $container->get('entity_type.manager')->getDefinition('media'),
      $container->get('string_translation'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * @update
   */
  public function hideNameField(DrupalStyle $io) {
    $this->entityTypeManager->clearCachedDefinitions();

    /** @var MediaTypeInterface[] $media_types */
    $media_types = $this->mediaTypeStorage->loadMultiple();
    foreach ($media_types as $media_type) {
      $question = (string) $this->t('Do you want to ensure the "Name" field is hidden on the Embedded view mode for the @media_type media type?', [
        '@media_type' => $media_type->label(),
      ]);

      if ($io->confirm($question)) {
        entity_get_display('media', $media_type->id(), 'embedded')->removeComponent('name')->save();;
      }
    }
  }

}
