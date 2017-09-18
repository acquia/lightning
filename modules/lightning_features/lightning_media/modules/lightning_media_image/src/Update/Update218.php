<?php

namespace Drupal\lightning_media_image\Update;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Media Image 2.1.8.
 *
 * @Update("2.1.8")
 */
final class Update218 implements ContainerInjectionInterface {

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The form display entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $formDisplayStorage;

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Update218 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $form_display_storage
   *   The form display entity storage handler.
   * @param string $app_root
   *   The Drupal application root.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ModuleInstallerInterface $module_installer, EntityStorageInterface $form_display_storage, $app_root, ConfigFactoryInterface $config_factory) {
    $this->moduleInstaller = $module_installer;
    $this->formDisplayStorage = $form_display_storage;
    $this->appRoot = (string) $app_root;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer'),
      $container->get('entity_type.manager')->getStorage('entity_form_display'),
      $container->get('app.root'),
      $container->get('config.factory')
    );
  }

  /**
   * @update
   *
   * @ask Do you want to enable image cropping and use it for image media?
   */
  public function imageCropping() {
    $this->moduleInstaller->install(['image_widget_crop']);

    // Use the cropping widgets for every form display of the Image media type.
    // This code is lifted almost directly from lightning_media_image_install().
    $form_displays = $this->formDisplayStorage->loadByProperties([
      'targetEntityType' => 'media',
      'bundle' => 'image',
    ]);

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    foreach ($form_displays as $form_display) {
      $component = $form_display->getComponent('image');
      $component['type'] = 'image_widget_crop';
      $component['settings']['crop_list'] = ['freeform'];
      $component['settings']['show_crop_area'] = TRUE;

      if ($form_display->getMode() == 'media_browser') {
        $component['third_party_settings']['lightning_media'] = [
          'file_links' => FALSE,
          'remove_button' => FALSE,
        ];
      }

      $form_display->setComponent('image', $component);
      $this->formDisplayStorage->save($form_display);
    }

    $cropper = 'libraries/cropper/dist/cropper.min';
    if (file_exists($this->appRoot . "/$cropper.js")) {
      $this->configFactory
        ->getEditable('image_widget_crop.settings')
        ->set('settings.library_url', "$cropper.js")
        ->set('settings.css_url', "$cropper.css")
        ->save();
    }
  }

}
