<?php

namespace Drupal\lightning_workflow\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm as BaseNodeForm;
use Drupal\workbench_moderation\Entity\ModerationState;

class NodeForm extends BaseNodeForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (isset($form['moderation_state']) && $this->moderationInfo()->isModeratedEntityForm($this)) {
      static::alterModerationState($form['moderation_state']['widget'][0]);

      // Show the basic 'Save' button, but hide the confusing
      // 'Save as unpublished' and 'Save and publish' buttons.
      $form['actions']['submit']['#access'] = TRUE;
      $form['actions']['publish']['#access'] = $form['actions']['unpublish']['#access'] = FALSE;
      unset($form['actions']['publish']['#dropbutton'], $form['actions']['unpublish']['#dropbutton']);

      // Workbench Moderation enforces revisions by checking and disabling the
      // revision checkbox. We don't need to display that.
      $form['revision']['#type'] = 'hidden';
      unset($form['revision_log']['#states']['visible'][':input[name="revision"]']);
    }

    return $form;
  }

  /**
   * Alters a moderation_state form element.
   *
   * Workbench Moderation's out-of-the-box UX is kind of awful, so this restores
   * a measure of sanity by making the moderation_state widget into a select list
   * and preventing it from messing around with the form's submit buttons.
   *
   * @param array $element
   *   The element to modify.
   */
  public static function alterModerationState(array &$element) {
    // Allow the user to choose the moderation state.
    $element['#access'] = TRUE;

    // Show the original labels of the destination states.
    foreach (array_keys($element['#options']) as $id) {
      $element['#options'][$id] = ModerationState::load($id)->label();
    }

    // Do NOT rejigger the form's buttons.
    unset($element['#process'][0]);
  }

  /**
   * Returns the moderation information service.
   *
   * @return \Drupal\workbench_moderation\ModerationInformationInterface
   *   The moderation information service.
   */
  protected function moderationInfo() {
    return @$this->moderationInfo ?: \Drupal::service('workbench_moderation.moderation_information');
  }

}
