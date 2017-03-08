<?php

namespace Drupal\lightning_workflow\Plugin\views\field;

use Drupal\Core\Render\Markup;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A Views field to indicate if a content entity has forward revision(s).
 *
 * @ViewsField("forward_revision_exists")
 */
class ForwardRevisionExists extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // This depends on the view having a relationship to the latest revision.
    // It's not clear if it's even possible for this handler to enforce that,
    // though, so we'll make do with a soft check.
    $rel = 'latest_revision__' . $this->view->getBaseEntityType()->id();

    if (empty($this->view->relationship[$rel])) {
      return NULL;
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $current */
    $current = $values->_entity;

    if (!isset($values->_relationship_entities[$rel])) {
      return NULL;
    }
    /** @var \Drupal\Core\Entity\ContentEntityInterface $latest */
    elseif (($latest = $values->_relationship_entities[$rel]) && ($latest->getRevisionId() > $current->getRevisionId())) {
      return Markup::create('&#10003;');
    }
    else {
      return NULL;
    }
  }

}
