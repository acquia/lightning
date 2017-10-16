<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormState;
use Drupal\Tests\BrowserTestBase;
use Drupal\lightning_api\Form\OAuthKeyForm;

abstract class ApiTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning_headless';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['api_test'];

  /**
   * The access token returned by the API.
   *
   * @var string
   */
  protected $accessToken;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Generate and store keys for use by OAuth.
    $this->generateKeys();
  }

  /**
   * Gets a token from the oauth endpoint for use in requests that require
   * authorization.
   *
   * @param array $client_options
   *   An array of options to use when requesting the token.
   *
   * @return string
   *   The OAuth2 password grant access token from the API.
   */
  protected function getToken($client_options) {
    $client = \Drupal::httpClient();
    $url = $this->buildUrl('/oauth/token');

    $response = $client->post($url, $client_options);
    $body = Json::decode($response->getBody());

    // The response should have an access token.
    $this->assertArrayHasKey('access_token', $body);

    return $body['access_token'];
  }

  /**
   * Generates and store OAuth keys.
   */
  protected function generateKeys() {
    $dir = drupal_realpath('temporary://');

    $form_state = (new FormState)->setValues([
      'dir' => $dir,
      'private_key' => 'private.key',
      'public_key' => 'public.key',
    ]);

    $this->container
      ->get('form_builder')
      ->submitForm(OAuthKeyForm::class, $form_state);
  }

  /**
   * Makes a request to the API using an optional OAuth token.
   *
   * @param string $endpoint
   *   Path to the API endpoint.
   * @param string $method
   *   The RESTful verb.
   * @param string $token
   *   A valid OAuth token to send as an Authorization header with the request.
   * @param array $data
   *   Additional json data to send with the request.
   *
   * @return \GuzzleHttp\Client
   *   A guzzle http client instance.
   */
  protected function request($endpoint, $method = 'get', $token = NULL, $data = NULL) {
    $client = \Drupal::httpClient();

    $options = NULL;
    if ($token) {
      $options = [
        'headers' => [
          'Authorization' => 'Bearer ' . $token,
          'Content-Type' => 'application/vnd.api+json'
        ],
      ];
    }
    if ($data) {
      $options['json'] = $data;
    }

    $url = $this->buildUrl($endpoint);

    return $client->$method($url, $options);
  }

}
