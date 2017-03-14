<?php

namespace Drupal\lightning_preview\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\workbench_moderation\Form\ModerationStateForm as BaseModerationStateForm;

/**
 * Customized edit form for moderation states.
 *
 * This form allows a moderation state to be configured in such a way that if
 * a workspace is moved into this state, the workspace will be locked and no
 * changes can be made within it.
 */
class ModerationStateForm extends BaseModerationStateForm {

  /**
   * Loads the 'basic' workspace type config entity.
   *
   * @return \Drupal\multiversion\Entity\WorkspaceTypeInterface
   *   The workspace type config entity.
   */
  protected function getWorkspaceType() {
    return $this->entityTypeManager->getStorage('workspace_type')->load('basic');
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $state = $this->getEntity()->id();

    $settings = $this->getWorkspaceType()
      ->getThirdPartySettings('workbench_moderation');

    if ($settings && $settings['enabled'] && in_array($state, $settings['allowed_moderation_states'])) {
      $form['lock_workspace'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Lock workspaces in this state'),
        '#default_value' => in_array($state, $settings['locked_states']),
        '#description' => $this->t('If checked, no changes can be made in a workspace when it reaches this state.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $workspace_type = $this->getWorkspaceType();
    $locked_states = $workspace_type->getThirdPartySetting('workbench_moderation', 'locked_states', []);
    $state = $this->getEntity()->id();

    if ($form_state->getValue('lock_workspace')) {
      $locked_states[] = $state;
      $locked_states = array_unique($locked_states);
    }
    else {
      $locked_states = array_diff($locked_states, [$state]);
    }

    $workspace_type
      ->setThirdPartySetting('workbench_moderation', 'locked_states', $locked_states)
      ->save();
  }

}
