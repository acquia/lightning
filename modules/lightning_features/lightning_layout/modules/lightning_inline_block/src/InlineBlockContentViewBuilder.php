<?php

namespace Drupal\lightning_inline_block;

use Drupal\block_content\BlockContentViewBuilder;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;

class InlineBlockContentViewBuilder extends BlockContentViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    parent::alterBuild($build, $entity, $display, $view_mode);

    $build['#attached']['library'][] = 'lightning_inline_block/quickedit';

    if (isset($build['#contextual_links']['block_content'])) {
      $build['#contextual_links']['inline_block_content'] = [
        'route_parameters' => [
          'inline_block_content' => $entity->id(),
        ],
        'metadata' => [
          'changed' => $entity->getChangedTime(),
        ],
      ];
      unset($build['#contextual_links']['block_content']);
    }
  }

}
