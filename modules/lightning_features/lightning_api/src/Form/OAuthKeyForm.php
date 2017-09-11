<?php

namespace Drupal\lightning_api\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_api\Exception\KeyGenerationException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OAuthKeyForm extends ConfigFormBase {

  /**
   * The UNIX permission bits to apply to the generated keys.
   *
   * @var integer
   */
  const PERMISSIONS = 0400;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * OAuthKeyForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param TranslationInterface $translation
   *   The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, TranslationInterface $translation) {
    parent::__construct($config_factory);
    $this->fileSystem = $file_system;
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system'),
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
   * Ensures that a configured key exists and has correct permissions.
   *
   * @param string $which
   *   The key to check. Can be 'public' or 'private'.
   *
   * @return bool
   *   TRUE if the key exists and has 600 permissions, FALSE otherwise.
   */
  private function keyExists($which) {
    $key = $this->config('simple_oauth.settings')->get("{$which}_key");

    return $key && file_exists($key) && (fileperms($key) & 0777) === static::PERMISSIONS;
  }

  /**
   * Writes a key to the file system.
   *
   * @param string $destination
   *   The desired destination of the key. Can be a directory or a full path.
   * @param string $key
   *   The data to write.
   *
   * @return string
   *   The final path of the written key.
   *
   * @throws \RuntimeException if an I/O error occurred while writing the key.
   */
  private function writeKey($destination, $key) {
    $destination = rtrim($destination, '/');

    if (is_dir($destination)) {
      $destination .= '/' . hash('sha256', $key) . '.key';
    }

    if (file_put_contents($destination, $key)) {
      $this->fileSystem->chmod($destination, static::PERMISSIONS);
      return $destination;
    }
    else {
      throw new \RuntimeException('The key could not be written.');
    }
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

    if ($form_state->isSubmitted() == FALSE) {
      if ($this->keyExists('public')) {
        drupal_set_message($this->t('A public key already exists and will be overwritten if you generate new keys.'), 'warning');
      }
      if ($this->keyExists('private')) {
        drupal_set_message($this->t('A private key already exists and will be overwritten if you generate new keys.'), 'warning');
      }
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
      list ($private_key, $public_key) = static::generateKeyPair($conf);
    }
    catch (KeyGenerationException $e) {
      return $this->onException($e, $form_state);
    }

    $dir = rtrim($form_state->getValue('dir'), '/');
    $config = $this->config('simple_oauth.settings');

    try {
      $destination = $dir . '/' . trim($form_state->getValue('private_key'));
      $destination = $this->writeKey($destination, $private_key);
      $config->set('private_key', $destination);

      $destination = $dir . '/' . trim($form_state->getValue('public_key'));
      $destination = $this->writeKey($destination, $public_key);
      $config->set('public_key', $destination);

      $config->save();
    }
    catch (\RuntimeException $e) {
      return $this->onException($e, $form_state);
    }

    drupal_set_message($this->t('A key pair was generated successfully.'));
  }

  /**
   * Generates an asymmetric key pair for OAuth authentication.
   *
   * @param array $options
   *   (optional) Additional configuration to pass to OpenSSL functions.
   *
   * @return string[]
   *   Returns the private and public key components, in that order.
   *
   * @throws \Drupal\lightning_api\Exception\KeyGenerationException
   *   If an error occurs during key generation or storage.
   */
  public static function generateKeyPair(array $options = []) {
    if (extension_loaded('openssl') == FALSE) {
      throw new KeyGenerationException('The OpenSSL PHP extension is unavailable');
    }

    $options += [
      'private_key_bits' => 2048,
      'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    $key_pair = [NULL];

    $pk = openssl_pkey_new($options);
    if (empty($pk)) {
      throw new KeyGenerationException();
    }

    // Get the private key as a string.
    $victory = openssl_pkey_export($pk, $key_pair[0], NULL, $options);
    if (empty($victory)) {
      throw new KeyGenerationException();
    }

    // Get the public key as a string.
    $key = openssl_pkey_get_details($pk)['key'];
    if (empty($key)) {
      throw new KeyGenerationException();
    }
    array_push($key_pair, $key);

    openssl_pkey_free($pk);
    return $key_pair;
  }

}
