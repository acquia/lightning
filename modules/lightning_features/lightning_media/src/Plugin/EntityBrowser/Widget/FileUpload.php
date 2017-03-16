<?php

namespace Drupal\lightning_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\lightning_media\Element\AjaxUpload;
use Drupal\lightning_media\SourceFieldTrait;
use Drupal\media_entity\MediaInterface;

/**
 * An Entity Browser widget for creating media entities from uploaded files.
 *
 * @EntityBrowserWidget(
 *   id = "file_upload",
 *   label = @Translation("File Upload"),
 *   description = @Translation("Allows creation of media entities from file uploads."),
 * )
 */
class FileUpload extends EntityFormProxy {

  use SourceFieldTrait;

  /**
   * {@inheritdoc}
   */
  protected function getInputValue(FormStateInterface $form_state) {
    return $form_state->getValue(['input', 'fid']);
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    $entities = parent::prepareEntities($form, $form_state);

    $get_file = function (MediaInterface $entity) {
      $type_config = $entity->getType()->getConfiguration();
      return $entity->get($type_config['source_field'])->entity;
    };

    if ($this->configuration['return_file']) {
      return array_map($get_file, $entities);
    }
    else {
      return $entities;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    $form['input'] = [
      '#type' => 'ajax_upload',
      '#title' => $this->t('File'),
      '#process' => [
        [$this, 'processUploadElement'],
      ],
      '#upload_validators' => [
        // For security, only allow extensions that are accepted by existing
        // media bundles.
        'file_validate_extensions' => [
          $this->getAcceptableExtensions(),
        ],
        // This must be a function because file_validate() is brain dead and
        // still thinks function_exists() is a good way to verify callability.
        'lightning_media_validate_upload' => [
          $this->getPluginId(),
          $this->getConfiguration(),
        ],
      ],
    ];

    return $form;
  }

  /**
   * Validates an uploaded file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The uploaded file.
   *
   * @return string[]
   *   An array of errors. If empty, the file passed validation.
   */
  public function validateFile(FileInterface $file) {
    $entity = $this->generateEntity($file);

    if (empty($entity)) {
      return [];
    }

    $type_config = $entity->getType()->getConfiguration();
    /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $item */
    $item = $entity->get($type_config['source_field'])->first();

    $validators = [
      // It's maybe a bit overzealous to run this validator, but hey...better
      // safe than screwed over by script kiddies.
      'file_validate_name_length' => [],
    ];
    $validators = array_merge($validators, $item->getUploadValidators());
    // If we got here, the extension is already validated (see ::getForm()).
    unset($validators['file_validate_extensions']);

    // If this is an image field, add image validation. Against all sanity,
    // this is normally done by ImageWidget, not ImageItem, which is why we
    // need to facilitate this a bit.
    if ($item instanceof ImageItem) {
      // Validate that this is, indeed, a supported image.
      $validators['file_validate_is_image'] = [];

      $settings = $item->getFieldDefinition()->getSettings();
      if ($settings['max_resolution'] || $settings['min_resolution']) {
        $validators['file_validate_image_resolution'] = [
          $settings['max_resolution'],
          $settings['min_resolution'],
        ];
      }
    }
    return file_validate($file, $validators);
  }

  /**
   * Returns an aggregated list of acceptable file extensions.
   *
   * @return string
   *   A space-separated list of file extensions accepted by the existing media
   *   bundles.
   */
  protected function getAcceptableExtensions() {
    $extensions = [];

    foreach ($this->bundleResolver->getPossibleBundles() as $bundle) {
      $source_field = $this->getSourceFieldForBundle($bundle);
      if ($source_field) {
        $extensions = array_merge($extensions, preg_split('/,?\s+/', $source_field->getSetting('file_extensions')));
      }
    }
    return implode(' ', array_unique($extensions));
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $input = $this->getInputValue($form_state);
    if ($input) {
      parent::validate($form, $form_state);
    }
    else {
      $form_state->setError($form['widget'], $this->t('You must upload a file.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $element['entity']['#entity'];

    $type_config = $entity->getType()->getConfiguration();
    /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $item */
    $item = $entity->get($type_config['source_field'])->first();
    /** @var FileInterface $file */
    $file = $item->entity;

    // Prepare the file's permanent home.
    $dir = $item->getUploadLocation();
    file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    $destination = $dir . '/' . $file->getFilename();
    if (!file_exists($destination)) {
      $file = file_move($file, $destination);
      $entity->set($type_config['source_field'], $file)->save();
    }
    $file->setPermanent();
    $file->save();

    $selection = [
      $this->configuration['return_file'] ? $file : $entity,
    ];
    $this->selectEntities($selection, $form_state);
  }

  /**
   * Processes the upload element.
   *
   * @param array $element
   *   The upload element.
   * @param FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The processed upload element.
   */
  public function processUploadElement(array $element, FormStateInterface $form_state) {
    $element = AjaxUpload::process($element, $form_state);

    $element['upload']['#ajax']['callback'] =
    $element['remove']['#ajax']['callback'] = [static::class, 'ajax'];

    $element['remove']['#value'] = $this->t('Cancel');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function ajax(array &$form, FormStateInterface $form_state) {
    $el = AjaxUpload::el($form, $form_state);

    $wrapper = '#' . $el['#ajax']['wrapper'];

    return parent::ajax($form, $form_state)
      // Replace the upload element with its rebuilt version.
      ->addCommand(
        new ReplaceCommand($wrapper, $el)
      )
      // Prepend the status messages so that a) any errors regarding the
      // uploaded file will be displayed right away, and b) the message queue
      // will be cleared so that the errors won't persist on a full page reload.
      ->addCommand(
        new PrependCommand($wrapper, ['#type' => 'status_messages'])
      );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['return_file'] = FALSE;
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['return_file'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Return source file entity'),
      '#default_value' => $this->configuration['return_file'],
      '#description' => $this->t('If checked, the source file(s) of the media entity will be returned from this widget.'),
    ];
    return $form;
  }

}
