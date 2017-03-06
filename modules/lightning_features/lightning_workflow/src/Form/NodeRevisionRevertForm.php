<?php

namespace Drupal\lightning_workflow\Form;

use Drupal\node\Form\NodeRevisionRevertForm as BaseForm;

/**
 * Changes verbiage in the core node revision revert form.
 */
class NodeRevisionRevertForm extends BaseForm {

  /**
   * Determines if the revision is a forward revision.
   *
   * @return bool
   *   TRUE if the revision is a forward revision, FALSE otherwise.
   */
  protected function isForwardRevision() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->nodeStorage->load($this->revision->id());
    return $this->revision->getRevisionId() > $node->getRevisionId();
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->isForwardRevision()) {
      $date = $this->dateFormatter->format($this->revision->getRevisionCreationTime());
      return $this->t('Are you sure you want to switch to the revision from %revision-date?', ['%revision-date' => $date]);
    }
    else {
      return parent::getQuestion();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->isForwardRevision() ? $this->t('Switch') : parent::getConfirmText();
  }

}
