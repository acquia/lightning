<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\consumers\Entity\Consumer;

/**
 * @group lightning
 * @group lightning_api
 * @group headless
 * @group api_test
 * @group foo
 */
class EntityTest extends ApiTestBase {

  /**
   * Options for the admin client.
   *
   * @var array
   */
  protected $admin_client_options = [
    'form_params' => [
      'grant_type' => 'password',
      'client_id' => 'api_test-admin-oauth2-client',
      'client_secret' => 'oursecret',
      'username' => 'api-admin-user',
      'password' => 'admin',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create an admin OAuth client that has permission to do everything.
    $edit = [
      'name' => 'api-admin-user',
      'mail' => 'api-admin-user@example.com',
      'pass' => 'admin',
    ];

    $account = User::create($edit);
    $account->addRole('administrator');
    $account->activate();
    $account->save();
    $api_admin_user_id = $account->id();

    // Create an associated client to use for testing.
    $data = [
      'uuid' => 'api_test-admin-oauth2-client',
      'label' => 'API Test Admin Client',
      'secret' => 'oursecret',
      'confidential' => 1,
      'user_id' => $api_admin_user_id,
      'roles' => 'administrator',
    ];

    $client = Consumer::create($data);
    $client->save();
  }

  /**
   * Tests create, read, and update of content and config entities via the
   * API.
   */
  public function testEntities() {
    $token = $this->getToken($this->admin_client_options);
    $description = 'Created by Carl Linnaeus';

    // Create and get a taxonomy vocabulary config entity.
    $data = [
      'data' => [
        'type' => 'taxonomy_vocabulary--taxonomy_vocabulary',
        'id' => 'taxonomy_test_vocabulary',
        'attributes' => [
          'uuid' => 'taxonomy_test_vocabulary',
          'name' => 'I\'m a vocab',
          'vid' => 'im_a_vocab',
          'description' => $description,
          'status' => TRUE,
        ]
      ]
    ];
    $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary', 'post', $token, $data);
    $response = $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'get', $token);
    $this->assertEquals(200, $response->getStatusCode());
    $body = Json::decode($response->getBody());
    $this->assertEquals($description, $body['data']['attributes']['description']);

    // Modify and get a taxonomy vocabulary config entity.
    $new_description = 'Refined by Johann Bartsch.';
    $data = [
      'data' => [
        'id' => 'taxonomy_test_vocabulary',
        'attributes' => [
          'description' => $new_description,
        ]
      ]
    ];
    $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'patch', $token, $data);
    $response = $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'get', $token);
    $this->assertEquals(200, $response->getStatusCode());
    $body = Json::decode($response->getBody());
    $this->assertEquals('I\'m a vocab', $body['data']['attributes']['name']);
    $this->assertEquals($new_description, $body['data']['attributes']['description']);

    // Assert that the newly created vocab endpoint is reachable.
    // @todo figure out why we need to rebuild caches for it to be available.
    drupal_flush_all_caches();
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab');
    $this->assertEquals(200, $response->getStatusCode());

    // Create and get a taxonomy term.
    $description = 'How quickly deft jumping zebras vex.';
    $data = [
      'data' => [
        'type' => 'taxonomy_term--im_a_vocab',
        'id' => 'zebra_taxonomy_term',
        'attributes' => [
          'name' => 'zebra',
          'uuid' => 'zebra_taxonomy_term',
          'description' => [
            'value' => $description,
            'format' => 'rich_text',
          ]
        ],
        'relationships' => [
          'vid' => [
            'data' => [
              'type' => 'taxonomy_vocabulary--taxonomy_vocabulary',
              'id' => 'taxonomy_test_vocabulary',
            ]
          ]
        ]
      ]
    ];
    $this->request('/jsonapi/taxonomy_term/im_a_vocab', 'post', $token, $data);
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'get', $token);
    $this->assertEquals(200, $response->getStatusCode());
    $body = Json::decode($response->getBody());
    $this->assertEquals($description, $body['data']['attributes']['description']['value']);

    // Modify and get a taxonomy term.
    $new_description = 'Smart squid gives lazy lummox who asks for job pen.';
    $data = [
      'data' => [
        'id' => 'zebra_taxonomy_term',
        'attributes' => [
          'description' => [
            'value' => $new_description,
          ]
        ]
      ]
    ];
    $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'patch', $token, $data);
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'get', $token);
    $this->assertEquals(200, $response->getStatusCode());
    $body = Json::decode($response->getBody());
    $this->assertEquals('zebra', $body['data']['attributes']['name']);
    $this->assertEquals($new_description, $body['data']['attributes']['description']['value']);
  }

}
