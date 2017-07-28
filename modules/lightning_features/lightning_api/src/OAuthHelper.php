<?php

namespace Drupal\lightning_api;

class OAuthHelper {

  /**
   * Generates an asymmetric key pair for OAuth authentication.
   *
   * @param bool $store
   *   (optional) Whether to store the generated key pair in the file system.
   *   Defaults to FALSE.
   *
   * @return string[]
   *   Returns the private and public key components, in that order. If $store
   *   is TRUE, returns the paths to the stored key components.
   *
   * @throws \Exception
   *   If an error occurs during key generation or storage.
   */
  public static function generateKeyPair($store = FALSE) {
    if (extension_loaded('openssl')) {
      $key_pair = [];

      // Throw this error if OpenSSL screws up during this procedure.
      $error = 'An internal error occurred';

      $pk = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
      ]);

      if (empty($pk)) {
        throw new \Exception($error);
      }

      $key_pair[0] = NULL;
      $victory = openssl_pkey_export($pk, $key_pair[0]);
      if (empty($victory)) {
        throw new \Exception($error);
      }

      $details = openssl_pkey_get_details($pk);
      if (isset($details['key'])) {
        $key_pair[1] = $details['key'];
      }
      else {
        throw new \Exception($error);
      }

      openssl_pkey_free($pk);

      return $store
        ? array_map([static::class, 'storeKey'], $key_pair)
        : $key_pair;
    }
    else {
      throw new \Exception('The OpenSSL PHP extension is unavailable');
    }
  }

  protected static function storeKey($key) {
    $path = sprintf('%s/%s.key', sys_get_temp_dir(), hash('sha256', $key));

    if (file_put_contents($path, $key)) {
      return $path;
    }
    else {
      throw new \Exception('The key could not be saved');
    }
  }

}
