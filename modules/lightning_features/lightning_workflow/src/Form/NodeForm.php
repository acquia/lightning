<?php

namespace Drupal\lightning_workflow\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm as BaseNodeForm;

/**
 * A moderation state-aware version of the node entity form.
 */
class NodeForm extends BaseNodeForm {

  use ModerationAwareEntityFormTrait {
    form as traitForm;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = $this->traitForm($form, $form_state);

    if ($this->moderationInfo()->isModeratedEntityForm($this)) {
      // Workbench Moderation enforces revisions by checking and disabling the
      // revision checkbox. We don't need to display that.
      $form['revision']['#type'] = 'hidden';
      unset($form['revision_log']['#states']['visible'][':input[name="revision"]']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    if ($this->moderationInfo()->isModeratedEntityForm($this)) {
      // Show the basic 'Save' button.
      $element['submit']['#access'] = TRUE;

      // Hide the 'Save and publish' button.
      $element['publish']['#access'] = FALSE;
      unset($element['publish']['#dropbutton']);

      // Hide the 'Save as unpublished' button.
      $element['unpublish']['#access'] = FALSE;
      unset($element['unpublish']['#dropbutton']);
    }
    return $element;
  }

}
