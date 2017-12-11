<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\ClientException;

/**
 * Tests that OAuth and json:api are working together to authenticate, authorize
 * and allow/forbid interaction with entities as designed.
 *
 * @group lightning
 * @group lightning_api
 * @group headless
 * @group api_test
 */
class ApiTest extends ApiTestBase {

  /**
   * OAuth token for the test client.
   *
   * @var string
   */
  private $token;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $page_creator_client_options = [
      'form_params' => [
        'grant_type' => 'password',
        'client_id' => 'api_test-oauth2-client',
        'client_secret' => 'oursecret',
        'username' => 'api-test-user',
        'password' => 'admin',
      ],
    ];
    $this->token = $this->getToken($page_creator_client_options);
  }

  /**
   * Tests Getting data as anon and authenticated user.
   */
  public function testAllowed() {
    // Get data that is available anonymously.
    $response = $this->request('/jsonapi/node/page/api_test-published-page-content');
    $this->assertEquals(200, $response->getStatusCode());
    $body = $this->decodeResponse($response);
    $this->assertEquals('Published Page', $body['data']['attributes']['title']);

    // Get data that requires authentication.
    $response = $this->request('/jsonapi/node/page/api_test-unpublished-page-content', 'get', $this->token);
    $this->assertEquals(200, $response->getStatusCode());
    $body = $this->decodeResponse($response);
    $this->assertEquals('Unpublished Page', $body['data']['attributes']['title']);

    // Post new content that requires authentication.
    $count = (int) \Drupal::entityQuery('node')->count()->execute();
    $data = [
      'data' => [
        'type' => 'node--page',
        'attributes' => [
          'title' => 'With my own two hands'
        ]
      ]
    ];
    $this->request('/jsonapi/node/page', 'post', $this->token, $data);
    $this->assertSame(++$count, (int) \Drupal::entityQuery('node')
      ->count()
      ->execute());

    // The user, client, and content should be removed on uninstall. The account
    // created by generateKeys() will still be around, but that exists only in
    // the test database, so we don't need to worry about it.
    \Drupal::service('module_installer')->uninstall(['api_test']);
    $this->assertSame(1, (int) \Drupal::entityQuery('user')->condition('uid', 1, '>')->count()->execute());
    $this->assertSame(0, (int) \Drupal::entityQuery('consumer')->count()->execute());
    $this->assertSame(0, (int) \Drupal::entityQuery('node')->count()->execute());
  }

  /**
   * Tests that authenticated and anonymous requests cannot get unauthorized
   * data.
   */
  public function testNotAllowed() {
    // Cannot get unauthorized data (not in role/scope) even when authenticated.
    $response = $this->request('/jsonapi/user_role/user_role', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertArrayHasKey('errors', $body['meta']);
    foreach ($body['meta']['errors'] as $error) {
      // This user/client should not have access to any of the roles' data. JSON
      // API will still return a 200, but with a list of 403 errors in the body.
      $this->assertEquals(403, $error['status']);
    }

    // Cannot get unauthorized data anonymously.
    $client = $this->container->get('http_client');
    $url = $this->buildUrl('/jsonapi/node/page/api_test-unpublished-page-content');
    // Unlike the roles test which requests a list, JSON API sends a 403 status
    // code when requesting a specific unauthorized resource instead of list.
    $this->setExpectedException(ClientException::class, 'Client error: `GET ' . $url . '` resulted in a `403 Forbidden`');
    $client->get($url);
  }

}