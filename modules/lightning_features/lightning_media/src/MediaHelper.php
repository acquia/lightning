<?php

namespace Drupal\lightning_media;

use Drupal\file\FileInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Provides helper methods for dealing with media entities.
 */
class MediaHelper {

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
