<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\taxonomy\Entity\Vocabulary;
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
    /** @var \Drupal\user\UserInterface $account */
    $account = User::create([
      'name' => 'api-admin-user',
      'mail' => 'api-admin-user@example.com',
      'pass' => 'admin',
    ]);
    $account->addRole('administrator');
    $account->activate()->save();

    // Create an associated OAuth client to use for testing.
    $client = Consumer::create([
      'label' => 'API Test Admin Client',
      'secret' => 'oursecret',
      'confidential' => 1,
      'user_id' => $account->id(),
      'roles' => 'administrator',
    ]);
    $client->save();

    // Retrieve and store a token to use in the requests.
    $admin_client_options = [
      'form_params' => [
        'grant_type' => 'password',
        'client_id' => $client->uuid(),
        'client_secret' => 'oursecret',
        'username' => $account->getAccountName(),
        'password' => 'admin',
      ],
    ];
    $this->token = $this->getToken($admin_client_options);
  }

  /**
   * Tests create, read, and update of content entities via the API.
   */
  public function testEntities() {
    // Create a taxonomy vocabulary. This cannot currently be done over the API
    // because jsonapi doesn't really support it, and will not be able to
    // properly support it until config entities can be internally validated
    // and access controlled outside of the UI.
    $vocabulary = Vocabulary::create([
      'name' => "I'm a vocab",
      'vid' => 'im_a_vocab',
      'status' => TRUE,
    ]);
    $vocabulary->save();

    $endpoint = '/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/' . $vocabulary->uuid();

    // Read the newly created vocabulary.
    $response = $this->request($endpoint, 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($vocabulary->label(), $body['data']['attributes']['name']);

    $vocabulary->set('name', 'Still a vocab, just a different title');
    $vocabulary->save();

    // Read the updated vocabulary.
    $response = $this->request($endpoint, 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($vocabulary->label(), $body['data']['attributes']['name']);

    // Assert that the newly created vocabulary's endpoint is reachable.
    // @todo figure out why we need to rebuild caches for it to be available.
    drupal_flush_all_caches();
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab');
    $this->assertEquals(200, $response->getStatusCode());

    $name = 'zebra';
    $term_uuid = $this->container->get('uuid')->generate();
    $endpoint = '/jsonapi/taxonomy_term/im_a_vocab/' . $term_uuid;
    $data = [
      'data' => [
        'type' => 'taxonomy_term--im_a_vocab',
        'id' => $term_uuid,
        'attributes' => [
          'name' => $name,
          'uuid' => $term_uuid,
        ],
        'relationships' => [
          'vid' => [
            'data' => [
              'type' => 'taxonomy_vocabulary--taxonomy_vocabulary',
              'id' => $vocabulary->uuid(),
            ]
          ]
        ]
      ]
    ];

    // Create a taxonomy term (content entity).
    $this->request('/jsonapi/taxonomy_term/im_a_vocab', 'post', $this->token, $data);

    // Read the taxonomy term.
    $response = $this->request($endpoint, 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($name, $body['data']['attributes']['name']);

    $new_name = 'squid';
    $data = [
      'data' => [
        'type' => 'taxonomy_term--im_a_vocab',
        'id' => $term_uuid,
        'attributes' => [
          'name' => $new_name,
        ]
      ]
    ];

    // Update the taxonomy term.
    $this->request($endpoint, 'patch', $this->token, $data);

    // Read the updated taxonomy term.
    $response = $this->request($endpoint, 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertSame($new_name, $body['data']['attributes']['name']);
  }

}