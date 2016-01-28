<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Plugin\Field\FieldFormatter\SVGAwareImageFormatter.
 */

namespace Drupal\lightning_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * A variant of the core Image formatter which skips image style processing for
 * SVG images.
 *
 * @FieldFormatter(
 *   id = "image_svg",
 *   label = @Translation("Image (SVG-aware)"),
 *   field_types = {"image"}
 * )
 */
class SVGAwareImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $build = parent::viewElements($items, $langcode);

    foreach ($build as $delta => $item) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $item['#item']->entity;

      if ($file->getMimeType() == 'image/svg+xml' || preg_match('/.svg$/', $file->getFileUri())) {
        $build[$delta]['#image_style'] = '';
      }
    }
    return $build;
  }

}
