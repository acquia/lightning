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

    $form_disabled = FALSE;
    $lightning_extensions = [
      'lightning_media',
      'lightning_layout',
      'lightning_workflow',
    ];

    $description = $this->t("You can choose to disable some of Lightning's functionality above. However, it is not recommended.");

    $yml_lightning_extensions = $this->extender->getLightningExtensions();
    if (is_array($yml_lightning_extensions)) {
      // Lightning Extensions are defined in the Extender so we set default
      // values according to the Extender, disable the checkboxes, and inform
      // the user.
      $lightning_extensions = $yml_lightning_extensions;
      $form_disabled = TRUE;
      $description = $this->t('Lightning Extensions have been set by the lightning.extend.yml file in your sites directory and are disabled here as a result.');
    }

    $form['extensions'] = [
      '#type' => 'checkboxes',
      '#description' => $description,
      '#disabled' => $form_disabled,
      '#options' => [
        'lightning_media' => $this->t('Lightning Media'),
        'lightning_layout' => $this->t('Lightning Layout'),
        'lightning_workflow' => $this->t('Lightning Workflow'),
      ],
      '#default_value' => $lightning_extensions,
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
