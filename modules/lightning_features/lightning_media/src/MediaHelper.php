<?php

namespace Drupal\lightning_media;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\lightning_media\Exception\IndeterminateBundleException;
use Drupal\media_entity\MediaInterface;

/**
 * Provides helper methods for dealing with media entities.
 */
class MediaHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MediaHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns all file extensions accepted by bundles that use file fields.
   *
   * @param bool $check_access
   *   (optional) Whether to filter the bundles by create access for the current
   *   user. Defaults to FALSE.
   *
   * @return string[]
   *   The file extensions accepted by all available bundles.
   */
  public function getFileExtensions($check_access = FALSE) {
    $extensions = '';

    // Lightning Media overrides the media_bundle storage handler with a special
    // one that adds an optional second parameter to loadMultiple().
    $bundles = $this->entityTypeManager
      ->getStorage('media_bundle')
      ->loadMultiple(NULL, $check_access);

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    foreach ($bundles as $bundle) {
      $type_plugin = $bundle->getType();

      if ($type_plugin instanceof SourceFieldInterface) {
        $field = $type_plugin->getSourceFieldDefinition($bundle);

        // If the field is a FileItem or any of its descendants, we can consider
        // it a file field. This will automatically include things like image
        // fields, which extend file fields.
        if (is_subclass_of($field->getItemDefinition()->getClass(), FileItem::class)) {
          $extensions .= ' ' . $field->getSetting('file_extensions');
        }
      }
    }
    $extensions = preg_split('/,?\s+/', $extensions);
    return array_unique($extensions);
  }

  /**
   * Returns the first media bundle that can accept an input value.
   *
   * @param mixed $value
   *   The input value.
   * @param bool $check_access
   *   (optional) Whether to filter the bundles by create access for the current
   *   user. Defaults to TRUE.
   *
   * @return \Drupal\media_entity\MediaBundleInterface
   *   A media bundle that can accept the input value.
   *
   * @throws \Drupal\lightning_media\Exception\IndeterminateBundleException if
   * no bundle can be matched to the input value.
   */
  public function getBundleFromInput($value, $check_access = TRUE) {
    // Lightning Media overrides the media_bundle storage handler with a special
    // one that adds an optional second parameter to loadMultiple().
    $bundles = $this->entityTypeManager
      ->getStorage('media_bundle')
      ->loadMultiple(NULL, $check_access);

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    foreach ($bundles as $bundle) {
      $type_plugin = $bundle->getType();

      if ($type_plugin instanceof InputMatchInterface && $type_plugin->appliesTo($value, $bundle)) {
        return $bundle;
      }
    }
    throw new IndeterminateBundleException($value);
  }

  /**
   * Creates a media entity from an input value.
   *
   * @param mixed $value
   *   The input value.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The unsaved media entity.
   */
  public function createFromInput($value) {
    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $this->entityTypeManager
      ->getStorage('media')
      ->create([
        'bundle' => $this->getBundleFromInput($value)->id(),
      ]);

    $field = static::getSourceField($entity);
    if ($field) {
      $field->setValue($value);
    }
    return $entity;
  }

  /**
   * Attaches a file entity to a media entity.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   * @param int $replace
   *   (optional) What to do if the file already exists. Can be any of the
   *   constants accepted by file_move().
   *
   * @return \Drupal\file\FileInterface|false
   *   The final file entity (unsaved), or FALSE if an error occurred.
   */
  public static function useFile(MediaInterface $entity, FileInterface $file, $replace = FILE_EXISTS_RENAME) {
    $field = static::getSourceField($entity);
    $field->setValue($file);

    $destination = static::prepareFileDestination($entity) . '/' . $file->getFilename();

    if ($destination == $file->getFileUri()) {
      return $file;
    }
    else {
      $final_file = file_move($file, $destination, $replace);

      if ($final_file) {
        $field->setValue($final_file);
        return $final_file;
      }
      else {
        return FALSE;
      }
    }
  }

  /**
   * Prepares the destination directory for a file attached to a media entity.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return string
   *   The destination directory URI.
   *
   * @throws \RuntimeException if the destination directory is not writable.
   */
  public static function prepareFileDestination(MediaInterface $entity) {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $item */
    $item = static::getSourceField($entity)->first();

    $dir = $item->getUploadLocation();
    $is_ready = file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    if ($is_ready) {
      return $dir;
    }
    else {
      throw new \RuntimeException('Could not prepare ' . $dir . ' for writing');
    }
  }

  /**
   * Indicates if the media entity's type plugin supports dynamic previews.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return bool
   *   TRUE if dynamic previews are supported, FALSE otherwise.
   */
  public static function isPreviewable(MediaInterface $entity) {
    $plugin_definition = $entity->getType()->getPluginDefinition();

    return isset($plugin_definition['preview']);
  }

  /**
   * Returns the media entity's source field item list.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The media entity's source field item list, or NULL if the media type
   *   plugin does not define a source field.
   */
  public static function getSourceField(MediaInterface $entity) {
    $type_configuration = $entity->getType()->getConfiguration();

    return isset($type_configuration['source_field'])
      ? $entity->get($type_configuration['source_field'])
      : NULL;
  }

}
