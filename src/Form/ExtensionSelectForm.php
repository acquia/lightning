<?php

namespace Drupal\lightning\Form;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning\Extender;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for selecting which Lightning extensions to install.
 */
class ExtensionSelectForm extends FormBase {

  /**
   * The Lightning extender configuration object.
   *
   * @var \Drupal\lightning\Extender
   */
  protected $extender;

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $root;

  /**
   * The info parser service.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * ExtensionSelectForm constructor.
   *
   * @param \Drupal\lightning\Extender $extender
   *   The Lightning extender configuration object.
   * @param string $root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(Extender $extender, $root, InfoParserInterface $info_parser, TranslationInterface $translator) {
    $this->extender = $extender;
    $this->root = $root;
    $this->infoParser = $info_parser;
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lightning.extender'),
      $container->get('app.root'),
      $container->get('info_parser'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_select_extensions';
  }

  /**
   * Extracts a set of elements from an array by key.
   *
   * @param array $keys
   *   The keys to extract.
   * @param array $values
   *   The array from which to extract the elements.
   *
   * @return array
   *   The extracted elements.
   */
  protected function pluck(array $keys, array $values) {
    return array_intersect_key($values, array_combine($keys, $keys));
  }

  /**
   * Yields info for each of Lightning's extensions.
   */
  protected function getExtensionInfo() {
    $extension_discovery = new ExtensionDiscovery($this->root);

    $extensions = $this->pluck(
      [
        'lightning_media',
        'lightning_layout',
        'lightning_workflow',
        'lightning_preview',
      ],
      $extension_discovery->scan('module')
    );

    /** @var \Drupal\Core\Extension\Extension $extension */
    foreach ($extensions as $key => $extension) {
      $info = $this->infoParser->parse($extension->getPathname());
      yield $key => $info;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('Extensions');

    $form['modules'] = [
      '#type' => 'checkboxes',
    ];
    $form['experimental'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Experimental'),
      '#tree' => TRUE,
    ];
    $form['experimental']['gate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand the <a href="@url" target="_blank">potential risks of experimental modules</a>', [
        '@url' => 'http://lightning.acquia.com/lightning-experimental-modules',
      ]),
    ];
    $form['experimental']['modules'] = [
      '#type' => 'checkboxes',
      '#process' => [
        // Apply normal checkbox processing...
        [
          Checkboxes::class,
          'processCheckboxes',
        ],
        // ...and our own special sauce.
        [
          __CLASS__,
          'addExperimentalGate',
        ],
      ],
    ];
    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
      ],
      '#type' => 'actions',
    ];

    foreach ($this->getExtensionInfo() as $key => $info) {
      if (empty($info['experimental'])) {
        $form['modules']['#options'][$key] = $info['name'];
        $form['modules']['#default_value'][] = $key;
      }
      else {
        $form['experimental']['modules']['#options'][$key] = $info['name'];
      }
    }

    // Hide the experimental section if there are no experimental extensions.
    $form['modules']['#access'] = (boolean) $form['experimental']['modules']['#options'];

    // If the extender configuration has a pre-selected set of extensions, don't
    // allow the user to choose different ones.
    $chosen_ones = $this->extender->getLightningExtensions();
    if (is_array($chosen_ones)) {
      // Prevent selection of non-experimental extensions.
      $form['modules']['#default_value'] = array_intersect(
        array_keys($form['modules']['#options']),
        $chosen_ones
      );
      $form['modules']['#disabled'] = TRUE;

      // Prevent selection of experimental extensions.
      $form['experimental']['modules']['#default_value'] = array_intersect(
        array_keys($form['experimental']['modules']['#options']),
        $chosen_ones
      );
      $form['experimental']['modules']['#disabled'] = TRUE;

      // Acknowledge the experimental gate.
      $form['experimental']['gate']['#disabled'] = TRUE;
      $form['experimental']['gate']['#default_value'] = TRUE;

      // Explain ourselves.
      drupal_set_message($this->t('Lightning extensions have been pre-selected in the lightning.extend.yml file in your sites directory.'), 'warning');
    }
    else {
      $form['modules']['#description'] = $this->t("You can choose to disable some of Lightning's functionality above. However, it is not recommended.");
    }

    return $form;
  }

  /**
   * Process function to hide an element behind the experimental gate.
   *
   * @param array $element
   *   The element to process.
   *
   * @return array
   *   The processed element.
   */
  public static function addExperimentalGate(array $element) {
    // The element is only visible if the experimental gate is acknowledged.
    foreach (Element::children($element) as $key) {
      $element[$key]['#states']['visible']['#edit-experimental-gate']['checked'] = TRUE;
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $modules = $form_state->getValue('modules');

    $experimental = $form_state->getValue('experimental');
    // Only install the experimental modules if they have explicitly accepted
    // the potential risks.
    if ($experimental['gate']) {
      $modules = array_merge($modules, $experimental['modules']);
    }
    $modules = array_filter($modules);

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
