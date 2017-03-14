<?php

namespace Drupal\lightning_preview\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lightning_workflow\Form\ModerationAwareEntityFormTrait;
use Drupal\workspace\Entity\Form\WorkspaceForm as BaseWorkspaceForm;

/**
 * A moderation-aware version of the workspace entity form.
 */
class WorkspaceForm extends BaseWorkspaceForm {

  use ModerationAwareEntityFormTrait {
    alterModerationState as traitAlterModerationState;
  }

  /**
   * {@inheritdoc}
   */
  public static function alterModerationState(array &$element, FormStateInterface $form_state) {
    static::traitAlterModerationState($element, $form_state);

    // Normally this would say 'this piece of content', which is weird if
    // you're editing a workspace.
    $element['#description'] = t('The moderation state of this workspace. &#128274; denotes a locked state. If you put this workspace into a locked state, you will be able switch into it and look around, but not make any changes.');

    $locked_states = $form_state
      ->getFormObject()
      ->getEntity()
      ->type
      ->entity
      ->getThirdPartySetting('workbench_moderation', 'locked_states', []);

    foreach ($element['#options'] as $id => $label) {
      if (in_array($id, $locked_states)) {
        $element['#options'][$id] = new FormattableMarkup('@label &#128274;', ['@label' => $label]);
      }
    }
  }

}
