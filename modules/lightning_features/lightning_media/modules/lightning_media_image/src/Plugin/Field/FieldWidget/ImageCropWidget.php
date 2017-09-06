<?php

namespace Drupal\lightning_media_image\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\crop\Entity\CropType;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget as BaseImageCropWidget;

class ImageCropWidget extends BaseImageCropWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['crop_list'] = [];
    $settings['warn_multiple_usages'] = FALSE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['crop_list']['#description'] = $this->t('The <a href="@crop_types">crop types</a> to allow. Only crop types that are associated with at least one <a href="@image_styles">image style</a> are shown here. If none are selected, all will be allowed.', [
      '@crop_types' =>
        Url::fromRoute('crop.overview_types')->toString(),
      '@image_styles' =>
        Url::fromRoute('entity.image_style.collection')->toString(),
    ]);
    $element['crop_list']['#required'] = FALSE;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key) {
    $value = parent::getSetting($key);

    // If no crop types are chosen, allow all of them.
    if ($key == 'crop_list' && empty($value)) {
      $value = CropType::getCropTypeNames();
      $value = $this->imageWidgetCropManager->getAvailableCropType($value);
      $value = array_keys($value);
    }
    return $value;
  }

}
