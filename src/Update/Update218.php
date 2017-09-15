<?php

namespace Drupal\lightning\Update;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\lightning_core\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning 2.1.8.
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
   * The view entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewStorage;

  public function __construct(ModuleInstallerInterface $module_installer, EntityStorageInterface $form_display_storage, $app_root, ConfigFactoryInterface $config_factory, EntityStorageInterface $view_storage = NULL) {
    $this->moduleInstaller = $module_installer;
    $this->formDisplayStorage = $form_display_storage;
    $this->appRoot = (string) $app_root;
    $this->configFactory = $config_factory;
    $this->viewStorage = $view_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity_type.manager');

    $arguments = [
      $container->get('module_installer'),
      $entity_manager->getStorage('entity_form_display'),
      $container->get('app.root'),
      $container->get('config.factory'),
    ];

    if ($container->get('module_handler')->moduleExists('views')) {
      $arguments[] = $entity_manager->getStorage('view');
    }

    return (new \ReflectionClass(__CLASS__))->newInstanceArgs($arguments);
  }

  /**
   * @update
   *
   * @ask Do you want to enable image cropping and use it for images?
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

  /**
   * @update
   *
   * @ask Do you want to enable bulk media upload?
   */
  public function bulkUpload() {
    $this->moduleInstaller->install(['lightning_media_bulk_upload']);
  }

  /**
   * @update
   *
   * @ask Do you want to make the Operations drop-button the last column in each
   * row of the Content view?
   */
  public function moveContentViewOperations() {
    if (empty($this->viewStorage)) {
      return;
    }

    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = $this->viewStorage->load('content');
    if (empty($view)) {
      return;
    }

    $display = &$view->getDisplay('default');

    if (isset($display['display_options']['fields']['operations'])) {
      Element::toTail($display['display_options']['fields'], 'operations');
      $this->viewStorage->save($view);
    }
  }

}
