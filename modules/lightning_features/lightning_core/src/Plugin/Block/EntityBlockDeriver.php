<?php

namespace Drupal\lightning_core\Plugin\Block;

use Drupal\ctools\Plugin\Deriver\EntityViewDeriver;

/**
 * Deriver for the entity_block block plugin.
 */
class EntityBlockDeriver extends EntityViewDeriver {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $derivatives = parent::getDerivativeDefinitions($base_plugin_definition);

    foreach ($derivatives as $id => $derivative) {
      $entity_type = $this->entityManager->getDefinition($id);

      $derivative['admin_label'] = $entity_type->getLabel();
      $derivative['description'] = $this->t('Displays a single @entity_type.', [
        '@entity_type' => $entity_type->getSingularLabel(),
      ]);
      unset($derivative['context']);

      $this->derivatives[$id] = $derivative;
    }
    return $this->derivatives;
  }

}
