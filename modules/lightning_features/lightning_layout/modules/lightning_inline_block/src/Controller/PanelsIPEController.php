<?php

namespace Drupal\lightning_inline_block\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\panels_ipe\Controller\PanelsIPEPageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PanelsIPEController extends PanelsIPEPageController {

  /**
   * {@inheritdoc}
   */
  public function getBlockContentForm($panels_storage_type, $panels_storage_id, $type, $block_content_uuid = NULL, Request $request = NULL) {
    $access = $this
      ->entityTypeManager()
      ->getAccessControlHandler('inline_block_content')
      ->createAccess($type, NULL, [], TRUE);

    if ($access->isForbidden()) {
      throw new AccessDeniedHttpException();
    }

    $block = $this
      ->entityTypeManager()
      ->getStorage('inline_block_content')
      ->create([
        'type' => $type,
      ]);

    $form = $this->entityFormBuilder()->getForm($block, 'panels_ipe', [
      'display' =>  $this->loadPanelsDisplay($panels_storage_type, $panels_storage_id),
    ]);

    return (new AjaxResponse())
      ->addCommand(
        new AppendCommand('.ipe-block-form', $form)
      );
  }

}
