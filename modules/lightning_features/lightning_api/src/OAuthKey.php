<?php

namespace Drupal\lightning_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\lightning_api\Exception\KeyGenerationException;

class OAuthKey {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * OAuthKey constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->fileSystem = $file_system;
  }

  /**
   * The UNIX permission bits to set on keys when writing.
   *
   * @var integer
   *
   * @see ::write()
   */
  const PERMISSIONS = 0600;

  /**
   * Checks if one or both OAuth key components exist.
   *
   * @param string $which
   *   (optional) Which key component to check. Can be 'public' or 'private'. If
   *   omitted, both components are checked.
   *
   * @return bool
   *   TRUE if the key component(s) exist, FALSE otherwise.
   */
  public function exists($which = NULL) {
    if ($which) {
      $key = $this->configFactory
        ->get('simple_oauth.settings')
        ->get("{$which}_key");

      return (
        $key &&
        file_exists($key) &&
        (fileperms($key) & 0777) === static::PERMISSIONS
      );
    }
    else {
      return $this->exists('private') && $this->exists('public');
    }
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
  public function write($destination, $key) {
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
  public static function generate(array $options = []) {
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
