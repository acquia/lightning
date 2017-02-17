<?php

namespace Acquia\LightningExtension\Context;

use Drupal\Component\Utility\Bytes;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\field\Entity\FieldConfig;

/**
 * Contains steps for working with fields at the API level.
 */
class FieldApiContext extends DrupalSubContextBase {

  /**
   * Sets the maximum upload size for a file or image field.
   *
   * @param string $field
   *   The field config entity ID.
   * @param string $size
   *   (optional) The maximum upload size, e.g. 50 KB, 32 MB, etc.
   *
   * @Given :field has a maximum upload size of :size
   * @Given :field has no maximum upload size
   *
   * @When I set the maximum upload size of :field to :size
   * @When I clear the maximum upload size of :field
   */
  public function setMaximumUploadSize($field, $size = NULL) {
    $field = FieldConfig::load($field);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $undo->push([$this, __FUNCTION__], [
        $field->id(),
        $field->getSetting('max_filesize'),
      ]);
    }

    if ($size) {
      $size = Bytes::toInt($size);
    }
    $field->setSetting('max_filesize', $size)->save();
  }

  /**
   * Sets the allowed extensions for a file or image field.
   *
   * @param string $field
   *   The field config entity ID.
   * @param string $extensions
   *   A space-separated list of allowed extensions.
   *
   * @Given :field accepts the :extensions extension(s)
   * @Given :field accepts :extensions files
   *
   * @When I configure :field to accept the :extensions extension(s)
   * @When I configure :field to accept :extensions files
   */
  public function setAllowedExtensions($field, $extensions) {
    $field = FieldConfig::load($field);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $undo->push([$this, __FUNCTION__], [
        $field->id(),
        $field->getSetting('file_extensions'),
      ]);
    }

    $field->setSetting('file_extensions', $extensions)->save();
  }

}
