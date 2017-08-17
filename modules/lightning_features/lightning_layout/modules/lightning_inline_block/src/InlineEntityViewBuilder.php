<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

class InlineEntityViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'full', $langcode = NULL) {
    $build_list = parent::viewMultiple($entities, $view_mode, $langcode);
    // Apply the buildMultiple() #pre_render callback immediately, to make
    // bubbling of attributes and contextual links to the actual block work.
    // @see \Drupal\block\BlockViewBuilder::buildBlock()
    unset($build_list['#pre_render'][0]);
    return $this->buildMultiple($build_list);
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    parent::alterBuild($build, $entity, $display, $view_mode);

    // Add contextual links for this entity.
    if ($entity->isNew() == FALSE) {
      $entity_type = $entity->getEntityTypeId();

      $build['#contextual_links'][$entity_type]['route_parameters'][$entity_type] = $entity->id();

      if ($entity instanceof EntityChangedInterface) {
        $build['#contextual_links'][$entity_type]['metadata']['changed'] = $entity->getChangedTime();
      }
    }
  }

}
