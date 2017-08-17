<?php

namespace Drupal\lightning_inline_block\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class RefreshCommand implements CommandInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return ['command' => 'refresh'];
  }

}
