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
      ])
      ->setStorage([
        'storage_type' => $panels_storage_type,
        'storage_id' => $panels_storage_id,
        'temp_store_key' => $this->loadPanelsDisplay($panels_storage_type, $panels_storage_id)->getTempStoreId(),
      ]);

    $extra = [];
    if ($request && $request->headers->has('referer')) {
      $extra['referrer'] = $request->headers->get('referer');
    }
    $form = $this->entityFormBuilder()->getForm($block, 'panels_ipe', $extra);

    return (new AjaxResponse())
      ->addCommand(
        new AppendCommand('.ipe-block-form', $form)
      );
  }

}
