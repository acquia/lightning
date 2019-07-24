<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\field\Entity\FieldConfig;

/**
 * Contains steps for working with fields at the API level.
 *
 * @internal
 *   This is an internal part of Lightning's testing system and may be changed
 *   or removed at any time without warning. External code should not extend,
 *   instantiate, or rely on this class in any way! If you'd like to use any of
 *   these step definitions in your project, you should copy them into your own
 *   project.
 */
class FieldApiContext extends DrupalSubContextBase {

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
