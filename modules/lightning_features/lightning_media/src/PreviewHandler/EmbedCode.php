<?php

namespace Drupal\lightning_media\PreviewHandler;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\lightning_media\PreviewHandlerBase;

/**
 * Preview handler for media bundles which require an embed code.
 *
 * @deprecated in Lightning 2.0.5 and will be removed in Lightning 2.1.0. Media
 * type plugin definitions should add the 'preview' key instead.
 */
class EmbedCode extends PreviewHandlerBase {

  /**
   * AJAX callback. Returns the commands for displaying a live preview.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function getPreviewContent() {
    return new AjaxResponse();
  }

}
