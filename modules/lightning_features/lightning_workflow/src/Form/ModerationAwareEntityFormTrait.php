<?php

namespace Drupal\lightning_workflow\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\workbench_moderation\Entity\ModerationState;

/**
 * A trait for entity forms which need to be aware of moderation states.
 */
trait ModerationAwareEntityFormTrait {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (isset($form['moderation_state']) && $this->moderationInfo()->isModeratedEntityForm($this)) {
      static::alterModerationState(
        $form['moderation_state']['widget'][0],
        $form_state
      );
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
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public static function alterModerationState(array &$element, FormStateInterface $form_state) {
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
  private function moderationInfo() {
    return @$this->moderationInfo ?: \Drupal::service('workbench_moderation.moderation_information');
  }

}
