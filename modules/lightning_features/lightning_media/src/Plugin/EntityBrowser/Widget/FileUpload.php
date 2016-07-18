<?php

namespace Drupal\lightning_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\file\Element\ManagedFile;
use Drupal\lightning_media\BundleResolverInterface;
use Drupal\lightning_media\SourceFieldTrait;
use Drupal\media_entity\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * An Entity Browser widget for creating media entities from uploaded files.
 *
 * @EntityBrowserWidget(
 *   id = "file_upload",
 *   label = @Translation("File Upload"),
 *   description = @Translation("Allows creation of media entities from file uploads."),
 *   bundle_resolver = "file_upload"
 * )
 */
class FileUpload extends EntityFormProxy {

  use SourceFieldTrait;

  /**
   * The token replacement service.
   *
   * @var Token
   */
  protected $token;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * EmbedCode constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param BundleResolverInterface $bundle_resolver
   *   The media bundle resolver.
   * @param AccountInterface $current_user
   *   The currently logged in user.
   * @param Token $token
   *   The token replacement service.
   * @param FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager, BundleResolverInterface $bundle_resolver, AccountInterface $current_user, Token $token, FileSystemInterface $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_manager, $bundle_resolver, $current_user);
    $this->token = $token;
    $this->fileSystem = $file_system;
    $this->fieldStorage = $entity_manager->getStorage('field_config');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $bundle_resolver = $plugin_definition['bundle_resolver'];

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity.manager'),
      $container->get('plugin.manager.lightning_media.bundle_resolver')->createInstance($bundle_resolver),
      $container->get('current_user'),
      $container->get('token'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputValue(FormStateInterface $form_state) {
    $value = $form_state->getValue('file');
    if ($value) {
      return $this->entityTypeManager->getStorage('file')->load($value[0]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    $form['file'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#process' => [
        [ManagedFile::class, 'processManagedFile'],
        [$this, 'processInitialFileElement'],
      ],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $form['widget']['entity']['#entity'];

    $file = $this->getFile($entity);
    $file->setPermanent();
    $file->save();

    parent::submit($element, $form, $form_state);
  }

  /**
   * Returns the source file of a media entity.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return \Drupal\file\FileInterface
   *   The source file.
   */
  protected function getFile(MediaInterface $entity) {
    $field = $this->getSourceField($entity)->getName();
    return $entity->get($field)->entity;
  }

  /**
   * Returns the expected permanent URI of the source file of a media entity.
   *
   * The permanent URI is computed from field configuration values and might
   * change (i.e., FILE_EXISTS_RENAME) during file system operations.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return string
   *   The expected permanent URI.
   */
  protected function getPermanentUri(MediaInterface $entity) {
    $field = $this->getSourceField($entity);

    $uri = '';
    $uri .= $field->getFieldStorageDefinition()->getSetting('uri_scheme');
    $uri .= '://';
    $uri .= $this->token->replace($field->getSetting('file_directory'));
    if (substr($uri, -3) != '://') {
      $uri .= '/';
    }
    $uri .= $this->getFile($entity)->getFilename();

    return $uri;
  }

  /**
   * {@inheritdoc}
   */
  protected function generateEntity($input) {
    $entity = parent::generateEntity($input);

    $destination = $this->getPermanentUri($entity);
    $dir = $this->fileSystem->dirname($destination);
    $ready = file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    if ($ready) {
      $file = file_move($this->getFile($entity), $destination);

      if ($file) {
        $file->setTemporary();
        $file->save();
        return $entity->set($this->getSourceField($entity)->getName(), $file);
      }
    }
  }

  /**
   * Processes the file element that is NOT part of the entity form.
   *
   * @param array $element
   *   The file element.
   *
   * @return array
   *   The processed file element.
   */
  public function processInitialFileElement(array $element) {
    $element['upload_button']['#ajax']['callback'] = [$this, 'onUpload'];
    $element['remove_button']['#value'] = $this->t('Cancel');
    $element['remove_button']['#ajax']['callback'] = [$this, 'onRemove'];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function processEntityForm(array $entity_form) {
    $type_config = $entity_form['#entity']->getType()->getConfiguration();
    $field = $type_config['source_field'];

    if (isset($entity_form[$field])) {
      $entity_form[$field]['widget'][0]['#process'][] = [$this, 'processEntityFormFileElement'];
    }

    return parent::processEntityForm($entity_form);
  }

  /**
   * Processes the file element that IS part of the entity form.
   *
   * @param array $element
   *   The file element.
   *
   * @return array
   *   The processed file element.
   */
  public function processEntityFormFileElement(array $element) {
    $element['remove_button']['#access'] = FALSE;

    if ($element['#default_value']) {
      $key = 'file_' . $element['#default_value']['target_id'];
      $element[$key]['#access'] = FALSE;
    }

    return $element;
  }

  /**
   * AJAX callback. Responds when a file has been uploaded.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current HTTP request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function onUpload(array &$form, FormStateInterface $form_state, Request $request) {
    $response = ManagedFile::uploadAjaxCallback($form, $form_state, $request);

    $complete_form = $form_state->getCompleteForm();
    $selector = '#' . $complete_form['widget']['ief_target']['#id'];
    $content = $this->getEntityForm($complete_form, $form_state);

    $command = new HtmlCommand($selector, $content);
    $response->addCommand($command);
    return $response;
  }

  /**
   * AJAX callback. Responds when the uploaded file is removed.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current HTTP request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function onRemove(array &$form, FormStateInterface $form_state, Request $request) {
    $response = ManagedFile::uploadAjaxCallback($form, $form_state, $request);

    $complete_form = $form_state->getCompleteForm();
    $selector = '#' . $complete_form['widget']['ief_target']['#id'];

    $command = new InvokeCommand($selector, 'empty');
    return $response->addCommand($command);
  }

}
