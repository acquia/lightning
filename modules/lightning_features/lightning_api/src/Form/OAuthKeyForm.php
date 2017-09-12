<?php

namespace Drupal\lightning_api\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_api\Exception\KeyGenerationException;
use Drupal\lightning_api\OAuthKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OAuthKeyForm extends ConfigFormBase {

  /**
   * The OAuth key service.
   *
   * @var \Drupal\lightning_api\OAuthKey
   */
  protected $key;

  /**
   * OAuthKeyForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\lightning_api\OAuthKey $key
   *   The OAuth keys service.
   * @param TranslationInterface $translation
   *   The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, OAuthKey $key, TranslationInterface $translation) {
    parent::__construct($config_factory);
    $this->key = $key;
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('lightning_api.oauth_key'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['simple_oauth.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oauth_key_form';
  }

  /**
   * Handles exceptions caught during form submission.
   *
   * @param \Exception $e
   *   The caught exception.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  private function onException(\Exception $e, FormStateInterface $form_state) {
    drupal_set_message($e->getMessage(), 'error');
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (extension_loaded('openssl') == FALSE) {
      drupal_set_message($this->t('The OpenSSL extension is unavailable. Please enable it to generate OAuth keys.'), 'error');
      return $form;
    }

    if ($this->key->exists() && $form_state->isSubmitted() == FALSE && $form_state->isRebuilding() == FALSE) {
      drupal_set_message($this->t('A key pair already exists and will be overwritten if you generate new keys.'), 'warning');
    }

    $form['dir'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination'),
      '#description' => $this->t('Path to the directory in which to store the generated keys.'),
      '#required' => TRUE,
      '#element_validate' => [
        '::validateDestinationExists',
      ],
    ];
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
    ];
    $form['advanced']['private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Private key name'),
      '#description' => $this->t('File name of the generated private key. Will be automatically generated if left empty.'),
      '#element_validate' => [
        '::validateKeyFileName',
      ],
    ];
    $form['advanced']['public_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public key name'),
      '#description' => $this->t('File name of the generated public key. Will be automatically generated if left empty.'),
      '#element_validate' => [
        '::validateKeyFileName',
      ],
    ];
    $form['advanced']['conf'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenSSL configuration file'),
      '#description' => $this->t('Path to the openssl.cnf configuration file. PHP will attempt to auto-detect this if not specified.'),
    ];
    $form['generate'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate keys'),
    ];

    return $form;
  }

  /**
   * Ensures that the destination directory exists.
   *
   * @param array $element
   *   The form element being validated.
   * @param FormStateInterface $form_state
   *   The current form state.
   */
  public function validateDestinationExists(array &$element, FormStateInterface $form_state) {
    $dir = $element['#value'];

    if (is_dir($dir) == FALSE) {
      $form_state->setError(
        $element,
        $this->t('%dir does not exist.', ['%dir' => $dir])
      );
    }
  }

  /**
   * Ensures that a requested file name contains no illegal characters.
   *
   * @param array $element
   *   The form element being validated.
   * @param FormStateInterface $form_state
   *   The current form state.
   */
  public function validateKeyFileName(array &$element, FormStateInterface $form_state) {
    $value = $element['#value'];

    if (strpos($value, '/') !== FALSE) {
      $form_state->setError(
        $element,
        $this->t('%value is not a valid name for a key file.', ['%value' => $value])
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $conf = [];

    // Gather OpenSSL configuration values specified by the user.
    $config = $form_state->getValue('conf');
    if ($config) {
      $conf['config'] = $config;
    }

    try {
      list ($private_key, $public_key) = OAuthKey::generate($conf);
    }
    catch (KeyGenerationException $e) {
      return $this->onException($e, $form_state);
    }

    $dir = rtrim($form_state->getValue('dir'), '/');
    $config = $this->config('simple_oauth.settings');

    try {
      $destination = $dir . '/' . trim($form_state->getValue('private_key'));
      $destination = $this->key->write($destination, $private_key);
      $config->set('private_key', $destination);

      $destination = $dir . '/' . trim($form_state->getValue('public_key'));
      $destination = $this->key->write($destination, $public_key);
      $config->set('public_key', $destination);

      $config->save();
    }
    catch (\RuntimeException $e) {
      return $this->onException($e, $form_state);
    }

    drupal_set_message($this->t('A key pair was generated successfully.'));
  }

}
