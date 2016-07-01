<?php

namespace Drupal\lightning\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lightning\Extender;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for selecting which Lightning extensions to install.
 */
class ExtensionSelectForm extends FormBase {

  /**
   * Path to the site's directory (e.g. sites/default)
   *
   * @var string
   */
  protected $extender;

  /**
   * ExtensionSelectForm constructor.
   *
   * @param Extender $extender
   *   The extender configuration object.
   */
  public function __construct(Extender $extender) {
    $this->extender = $extender;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lightning.extender')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_select_extensions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('Extensions');

    $form['extensions'] = [
      '#type' => 'checkboxes',
      '#description' => $this->t("You can choose to disable some of Lightning's functionality above. However, it is not recommended."),
      '#options' => [
        'lightning_media' => $this->t('Lightning Media'),
        'lightning_layout' => $this->t('Lightning Layout'),
        'lightning_workflow' => $this->t('Lightning Workflow'),
      ],
      '#default_value' => [
        'lightning_media',
        'lightning_layout',
        'lightning_workflow',
      ],
    ];

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
      ],
      '#type' => 'actions',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $modules = array_filter($form['extensions']['#value']);

    if (in_array('lightning_media', $modules)) {
      $modules[] = 'lightning_media_document';
      $modules[] = 'lightning_media_image';
      $modules[] = 'lightning_media_instagram';
      $modules[] = 'lightning_media_twitter';
      $modules[] = 'lightning_media_video';
    }

    $GLOBALS['install_state']['lightning']['modules'] = array_merge($modules, $this->extender->getModules());
  }

}
