<?php

namespace Drupal\lightning_inline_block\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class UpdatePanelsIPECommand implements CommandInterface {

  protected $attributes;

  public function __construct(array $attributes) {
    if ($attributes) {
      $this->attributes = $attributes;
    }
  }

  public static function unsaved() {
    return new static([
      'unsaved' => TRUE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'update_panels_ipe',
      'attributes' => $this->attributes,
    ];
  }

}
