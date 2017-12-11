<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Psr\Http\Message\ResponseInterface;

abstract class ApiTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['api_test'];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning';

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
    $client = $this->container->get('http_client');
    $url = $this->buildUrl('/oauth/token');

    $response = $client->post($url, $client_options);
    $body = $this->decodeResponse($response);

    // The response should have an access token.
    $this->assertArrayHasKey('access_token', $body);

    return $body['access_token'];
  }

  /**
   * Generates and store OAuth keys.
   */
  protected function generateKeys() {
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);
    $url = Url::fromRoute('lightning_api.generate_keys');
    $this->drupalGet($url);
    $values = [
      'dir' => drupal_realpath('temporary://'),
      'private_key' => 'private.key',
      'public_key' => 'public.key',
    ];
    $conf = getenv('OPENSSL_CONF');
    if ($conf) {
      $values['conf'] = $conf;
    }
    $this->drupalPostForm(NULL, $values, 'Generate keys');
    $this->assertSession()->pageTextContains('A key pair was generated successfully.');
    $this->drupalLogout();
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
   * @return \Psr\Http\Message\ResponseInterface
   *   The response from the request.
   */
  protected function request($endpoint, $method = 'get', $token = NULL, $data = NULL) {
    $client = $this->container->get('http_client');

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

  /**
   * Decodes a JSON response from the server.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response object.
   *
   * @return mixed
   *   The decoded response data. If the JSON parser raises an error, the test
   *   will fail, with the bad input as the failure message.
   */
  protected function decodeResponse(ResponseInterface $response) {
    $body = (string) $response->getBody();
    $data = Json::decode($body);
    if (json_last_error() === JSON_ERROR_NONE) {
      return $data;
    }
    else {
      $this->fail($body);
    }
  }

}