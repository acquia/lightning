<?php

namespace Drupal\lightning_inline_block;

interface InlineEntityStorageInterface {

  public function getStorageInfo(InlineEntityInterface $entity);

}
