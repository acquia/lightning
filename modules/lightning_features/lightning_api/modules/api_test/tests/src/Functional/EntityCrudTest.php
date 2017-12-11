<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\user\Entity\User;
use Drupal\consumers\Entity\Consumer;

/**
 * Tests the ability to Create, Read, and Update config and config entities via
 * the API.
 *
 * @group lightning
 * @group lightning_api
 * @group headless
 * @group api_test
 */
class EntityCrudTest extends ApiTestBase {

  /**
   * OAuth token for the admin client.
   *
   * @var string
   */
  private $token;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create an admin user that has permission to do everything for testing.
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

    // Create an associated OAuth client to use for testing.
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

    // Retrieve and store a token to use in the requests.
    $admin_client_options = [
      'form_params' => [
        'grant_type' => 'password',
        'client_id' => 'api_test-admin-oauth2-client',
        'client_secret' => 'oursecret',
        'username' => 'api-admin-user',
        'password' => 'admin',
      ],
    ];
    $this->token = $this->getToken($admin_client_options);
  }

  /**
   * Tests create, read, and update of content and config entities via the
   * API.
   */
  public function testEntities() {
    $description = 'Created by Carl Linnaeus';
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

    // Create a taxonomy vocabulary (config entity).
    $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary', 'post', $this->token, $data);

    // Read the newly created vocabulary.
    $response = $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($description, $body['data']['attributes']['description']);

    $new_description = 'Refined by Johann Bartsch.';
    $data = [
      'data' => [
        'id' => 'taxonomy_test_vocabulary',
        'attributes' => [
          'description' => $new_description,
        ]
      ]
    ];

    // Update the vocabulary.
    $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'patch', $this->token, $data);

    // Read the updated vocabulary.
    $response = $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals('I\'m a vocab', $body['data']['attributes']['name']);
    $this->assertEquals($new_description, $body['data']['attributes']['description']);

    // Assert that the newly created vocabulary's endpoint is reachable.
    // @todo figure out why we need to rebuild caches for it to be available.
    drupal_flush_all_caches();
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab');
    $this->assertEquals(200, $response->getStatusCode());

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

    // Create a taxonomy term (content entity).
    $this->request('/jsonapi/taxonomy_term/im_a_vocab', 'post', $this->token, $data);

    // Read the taxonomy term.
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($description, $body['data']['attributes']['description']['value']);

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

    // Update the taxonomy term.
    $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'patch', $this->token, $data);

    // Read the updated taxonomy term.
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertSame('zebra', $body['data']['attributes']['name']);
    $this->assertSame($new_description, $body['data']['attributes']['description']['value']);
  }

}
