<?php

namespace Drupal\lightning_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\image\Plugin\ImageEffect\ResizeImageEffect;

/**
 * SVG-aware variant of the core Image formatter.
 *
 * This formatter disables image style processing for SVG images. All other
 * image formats are passed along to the core image formatter.
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
      // If there's no image style applied, we don't need to do anything.
      if (empty($item['#image_style'])) {
        continue;
      }

      /** @var \Drupal\file\FileInterface $file */
      $file = $item['#item']->entity;

      // The core image toolkits don't support SVG, so if this is an SVG image,
      // we need to prevent any image style from being applied to it...but we
      // DO want to preserve any dimensions transformations applied by the image
      // style.
      if ($file->getMimeType() == 'image/svg+xml') {
        /** @var ImageStyleInterface $image_style */
        $image_style = $this->imageStyleStorage->load($item['#image_style']);

        // The SVG has no real intrinsic dimensions, so extract the largest
        // set of starting dimensions from the configured image effects.
        $dimensions = $this->getDimensions($image_style);
        if ($dimensions) {
          $this->sortDimensions($dimensions);
          $dimensions = end($dimensions);
        }
        else {
          $dimensions = [
            'width' => NULL,
            'height' => NULL,
          ];
        }

        // Run through the entire effect chain and allow them to transform the
        // dimensions however they want.
        foreach ($image_style->getEffects() as $effect) {
          $effect->transformDimensions($dimensions, $file->getFileUri());
        }
        // Output the width, if we have one.
        if (isset($dimensions['width'])) {
          $build[$delta]['#item_attributes']['width'] = $dimensions['width'];
        }

        // Don't apply the original image style.
        $build[$delta]['#image_style'] = '';
      }
    }
    return $build;
  }

  /**
   * Sorts a set of dimensions by a single axis.
   *
   * @param array $dimensions
   *   The dimensions to sort, as returned by ::getDimensions().
   * @param string $axis
   *   (optional) The axis on which the dimensions should be sorted. Can be
   *   'width' or 'height'.
   */
  protected function sortDimensions(array &$dimensions, $axis = 'width') {
    usort($dimensions, function (array $a, array $b) use ($axis) {
      return $b[$axis] - $a[$axis];
    });
  }

  /**
   * Extracts all configured dimensions from resize effects in an image style.
   *
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style.
   *
   * @return array
   *   The configured dimensions for all resize effects. Each item is an array
   *   containing 'width' and 'height' elements.
   */
  protected function getDimensions(ImageStyleInterface $image_style) {
    $dimensions = [];

    foreach ($image_style->getEffects() as $effect) {
      if ($effect instanceof ResizeImageEffect) {
        $configuration = $effect->getConfiguration();

        array_push($dimensions, [
          'width' => $configuration['data']['width'],
          'height' => $configuration['data']['height'],
        ]);
      }
    }

    return $dimensions;
  }

}
