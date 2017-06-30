<?php

namespace Drupal\lightning_inline_block;

interface InlineEntityStorageInterface {

  /**
   * Returns the Panels storage info for an inline entity.
   *
   * The Panels storage info is a row from the {inline_entity} tracking table,
   * containing at least the following properties:
   *
   * - storage_type: The Panels storage plugin ID.
   * - storage_id: The Panels display's storage ID, as known to the storage
   *   plugin.
   * - temp_store_id: (optional) The temp store ID for the Panels display's
   *   latest configuration (which is expected to contain an inline_entity
   *   block plugin instance identified by block_id).
   * - block_id: The UUID of the inline_entity block plugin instance that
   *   contains the entity, as known to the Panels display.
   *
   * @param \Drupal\lightning_inline_block\InlineEntityInterface $entity
   *   The inline entity.
   *
   * @return \stdClass|false
   *    The inline entity's storage info, or FALSE if there is none.
   */
  public function getStorageInfo(InlineEntityInterface $entity);

}
