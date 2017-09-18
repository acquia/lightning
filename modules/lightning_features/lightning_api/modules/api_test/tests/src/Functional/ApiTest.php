<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Exception\ClientException;

/**
 * @group headless
 * @group api_test
 */
class ApiTest extends BrowserTestBase {

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
   * URL strings for different endpoints.
   *
   * @var string[]
   */
  protected $paths = [
    'page_published_get' => '/jsonapi/node/page/api_test-published-page-content',
    'page_unpublished_get' => '/jsonapi/node/page/api_test-unpublished-page-content',
    'page_post' => '/jsonapi/node/page',
    'role_get' => '/jsonapi/user_role/user_role',
    'token_get' => '/oauth/token',
  ];

  /**
   * Tests Getting data as anon and authenticated user.
   */
  public function testAllowed() {
    // Get data that is available anonymously.
    $client = \Drupal::httpClient();
    $url = $this->buildUrl($this->paths['page_published_get']);
    $response = $client->get($url);
    $this->assertEquals(200, $response->getStatusCode());
    $body = Json::decode($response->getBody());
    $this->assertEquals('Published Page', $body['data']['attributes']['title']);

    // Get data that requires authentication.
    $token = $this->getToken();
    $url = $this->buildUrl($this->paths['page_unpublished_get']);
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/vnd.api+json'
      ],
    ];
    $response = $client->get($url, $options);
    $this->assertEquals(200, $response->getStatusCode());
    $body = Json::decode($response->getBody());
    $this->assertEquals('Unpublished Page', $body['data']['attributes']['title']);

    // Post new content that requires authentication.
    $count = (int) \Drupal::entityQuery('node')->count()->execute();
    $token = $this->getToken();
    $url = $this->buildUrl($this->paths['page_post']);
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/vnd.api+json'
      ],
      'json' => [
        'data' => [
          'type' => 'node--page',
          'attributes' => [
            'title' => 'With my own two hands'
          ]
        ]
      ]
    ];
    $client->post($url, $options);
    $this->assertSame(++$count, (int) \Drupal::entityQuery('node')->count()->execute());

    // The user, client, and content should be removed on uninstall.
    \Drupal::service('module_installer')->uninstall(['api_test']);
    $this->assertCount(0, \Drupal::entityQuery('user')->condition('uid', 1, '>')->execute());
    $this->assertCount(0, \Drupal::entityQuery('oauth2_client')->execute());
    $this->assertCount(0, \Drupal::entityQuery('node')->execute());
  }

  /**
   * Tests that authenticated and anonymous requests cannot get unauthorized
   * data.
   */
  public function testNotAllowed() {
    // Cannot get unauthorized data (not in role/scope) even when authenticated.
    $client = \Drupal::httpClient();
    $token = $this->getToken();
    $url = $this->buildUrl($this->paths['role_get']);
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/vnd.api+json'
      ],
    ];
    $response = $client->get($url, $options);
    $body = Json::decode($response->getBody());
    $this->assertArrayHasKey('errors', $body['meta']);
    foreach ($body['meta']['errors'] as $error) {
      // This user/client should not have access to any of the roles' data. JSON
      // API will still return a 200, but with a list of 403 errors in the body.
      $this->assertEquals(403, $error['status']);
    }

    // Cannot get unauthorized data anonymously.
    $url = $this->buildUrl($this->paths['page_unpublished_get']);
    // Unlike the roles test which requests a list, JSON API sends a 403 status
    // code when requesting a specific unauthorized resource instead of list.
    $this->setExpectedException(ClientException::class, 'Client error: `GET ' . $url . '` resulted in a `403 Forbidden`');
    $client->get($url);
  }

  /**
   * Gets a token from the oauth endpoint using the client and user created in
   * the API Test module. The client and user have the "Basic page creator" role
   * so requests that use the token generated here should inherit those
   * permissions.
   *
   * @return string
   *   The OAuth2 password grant access token from the API.
   */
  protected function getToken() {
    if ($this->accessToken) {
      return $this->accessToken;
    }
    $client = \Drupal::httpClient();
    // "api-test-user" user and "api_test-oauth2-client" oauth2_client have the
    // "Basic page creator" role/scope.
    $options = [
      'form_params' => [
        'grant_type' => 'password',
        'client_id' => 'api_test-oauth2-client',
        'client_secret' => 'oursecret',
        'username' => 'api-test-user',
        'password' => 'admin',
      ],
    ];
    $url = $this->buildUrl($this->paths['token_get']);

    $response = $client->post($url, $options);
    $body = Json::decode($response->getBody());

    // The response should have an access token.
    $this->assertArrayHasKey('access_token', $body);

    $this->accessToken = $body['access_token'];
    return $this->accessToken;
  }
}
