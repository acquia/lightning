<?php

namespace Drupal\Tests\lightning\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests the decoupled API shipped with the Lightning profile.
 *
 * @group lightning
 */
class ApiTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    $config_factory->getEditable('lightning_api.settings')
      ->set('entity_json', TRUE)
      ->save();

    // If the samlauth module is installed, ensure that it is configured (in
    // this case, using its own test data, copied here so as to not depend on
    // another module's test fixtures) to avoid errors when creating user
    // accounts in this test.
    if ($this->container->get('module_handler')->moduleExists('samlauth')) {
      $config_factory->getEditable('samlauth.authentication')
        ->setData([
          'sp_entity_id' => 'samlauth',
          'idp_entity_id' => 'https://idp.testshib.org/idp/shibboleth',
          'idp_single_sign_on_service' => 'https://idp.testshib.org/idp/profile/SAML2/Redirect/SSO',
          'idp_single_log_out_service' => '',
          'idp_x509_certificate' => 'MIIEDjCCAvagAwIBAgIBADANBgkqhkiG9w0BAQUFADBnMQswCQYDVQQGEwJVUzEVMBMGA1UECBMMUGVubnN5bHZhbmlhMRMwEQYDVQQHEwpQaXR0c2J1cmdoMREwDwYDVQQKEwhUZXN0U2hpYjEZMBcGA1UEAxMQaWRwLnRlc3RzaGliLm9yZzAeFw0wNjA4MzAyMTEyMjVaFw0xNjA4MjcyMTEyMjVaMGcxCzAJBgNVBAYTAlVTMRUwEwYDVQQIEwxQZW5uc3lsdmFuaWExEzARBgNVBAcTClBpdHRzYnVyZ2gxETAPBgNVBAoTCFRlc3RTaGliMRkwFwYDVQQDExBpZHAudGVzdHNoaWIub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArYkCGuTmJp9eAOSGHwRJo1SNatB5ZOKqDM9ysg7CyVTDClcpu93gSP10nH4gkCZOlnESNgttg0r+MqL8tfJC6ybddEFB3YBo8PZajKSe3OQ01Ow3yT4I+Wdg1tsTpSge9gEz7SrC07EkYmHuPtd71CHiUaCWDv+xVfUQX0aTNPFmDixzUjoYzbGDrtAyCqA8f9CN2txIfJnpHE6q6CmKcoLADS4UrNPlhHSzd614kR/JYiks0K4kbRqCQF0Dv0P5Di+rEfefC6glV8ysC8dB5/9nb0yh/ojRuJGmgMWHgWk6h0ihjihqiu4jACovUZ7vVOCgSE5Ipn7OIwqd93zp2wIDAQABo4HEMIHBMB0GA1UdDgQWBBSsBQ869nh83KqZr5jArr4/7b+QazCBkQYDVR0jBIGJMIGGgBSsBQ869nh83KqZr5jArr4/7b+Qa6FrpGkwZzELMAkGA1UEBhMCVVMxFTATBgNVBAgTDFBlbm5zeWx2YW5pYTETMBEGA1UEBxMKUGl0dHNidXJnaDERMA8GA1UEChMIVGVzdFNoaWIxGTAXBgNVBAMTEGlkcC50ZXN0c2hpYi5vcmeCAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAjR29PhrCbk8qLN5MFfSVk98t3CT9jHZoYxd8QMRLI4j7iYQxXiGJTT1FXs1nd4Rha9un+LqTfeMMYqISdDDI6tv8iNpkOAvZZUosVkUo93pv1T0RPz35hcHHYq2yee59HJOco2bFlcsH8JBXRSRrJ3Q7Eut+z9uo80JdGNJ4/SJy5UorZ8KazGj16lfJhOBXldgrhppQBb0Nq6HKHguqmwRfJ+WkxemZXzhediAjGeka8nz8JjwxpUjAiSWYKLtJhGEaTqCYxCCX2Dw+dOTqUzHOZ7WKv4JXPK5G/Uhr8K/qhmFT2nIQi538n6rVYLeWj8Bbnl+ev0peYzxFyF5sQA==',
          'unique_id_attribute' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.10',
          'create_users' => 0,
          'sync_name' => 0,
          'sync_mail' => 0,
          'user_name_attribute' => 'email',
          'user_mail_attribute' => 'email',
          'idp_change_password_service' => '',
          'sp_x509_certificate' => 'MIIDozCCAoqgAwIBAgIBADANBgkqhkiG9w0BAQ0FADBrMQswCQYDVQQGEwJ1czEOMAwGA1UECAwFSWRhaG8xFTATBgNVBAoMDGN3ZWFnYW5zLm5ldDEVMBMGA1UEAwwMY3dlYWdhbnMubmV0MR4wHAYJKoZIhvcNAQkBFg9tZUBjd2VhZ2Fucy5uZXQwHhcNMTUwNjIzMjAwMjMyWhcNMjUwNjIwMjAwMjMyWjBrMQswCQYDVQQGEwJ1czEOMAwGA1UECAwFSWRhaG8xFTATBgNVBAoMDGN3ZWFnYW5zLm5ldDEVMBMGA1UEAwwMY3dlYWdhbnMubmV0MR4wHAYJKoZIhvcNAQkBFg9tZUBjd2VhZ2Fucy5uZXQwggEjMA0GCSqGSIb3DQEBAQUAA4IBEAAwggELAoIBAgDDZOeQF9Cp5k0WzNBye9S/3FgKxTZjcAPBFLtMMAhcx9+kLYMwS5J5h1OUKQcaoxmz/MiVKnrnozStdOKYIeS0C+8DmjRPjKEva77RYEy/Zu4l2Y+Nijt9/OMrO2JwuchHI9Xx+rqifDCR9rJ4vwbu/6/NhTVggSgsDsxlgGtLWC1zoUmwtcBe30t63P1eDrNAEg5EkM3y6OCsx6HaK7nAJmGaF6of/60UmEXB6qBVgZlU/qUmrVX89EdGvPrKWvYJcX3xAcIQh/on/1e/XmGMRYnBB6E0qyx6sL0ZmHzwH5jIUR5S1xwqWhSAjlOUHLSg2tYfHx0dn3UV2koY9QsKEQIDAQABo1AwTjAdBgNVHQ4EFgQUTJG5GAzq0olNiSfg7c/zjaBHnwcwHwYDVR0jBBgwFoAUTJG5GAzq0olNiSfg7c/zjaBHnwcwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQ0FAAOCAQIAsaxgtTmqQLbNETa7pLD0q0qCU7awxUUfwM73CwW+uIoZXeTz4YP6qaQ9T8kbBjyQKK9jmvTCHHm7y7U7a6NTj3DHuGpjo6mOXNMeh259iCSOfxpm2hMnUsLuQk3dM1+POJWSRQ8LFUcx9WT7siqvKfKq8cTjul7DMRR3MWOAgcd6N+ru3UYsuU81M0Gar417va+GkdMhoRBGT/K6jz9dkvOdQWmIlitYGvkQ6o7VGM5wB9J9wZ/A5FDJ0/IxGXJDD8aFtst8RTBwf8Cgptbmmycu9jqrby4bDLQ5ygviBZ7ZvXt4c9pOPWtXxvloilUNM992EVFyiJTTg9yniXcZSsU=',
          'sp_private_key' => 'MIIEwQIBADANBgkqhkiG9w0BAQEFAASCBKswggSnAgEAAoIBAgDDZOeQF9Cp5k0WzNBye9S/3FgKxTZjcAPBFLtMMAhcx9+kLYMwS5J5h1OUKQcaoxmz/MiVKnrnozStdOKYIeS0C+8DmjRPjKEva77RYEy/Zu4l2Y+Nijt9/OMrO2JwuchHI9Xx+rqifDCR9rJ4vwbu/6/NhTVggSgsDsxlgGtLWC1zoUmwtcBe30t63P1eDrNAEg5EkM3y6OCsx6HaK7nAJmGaF6of/60UmEXB6qBVgZlU/qUmrVX89EdGvPrKWvYJcX3xAcIQh/on/1e/XmGMRYnBB6E0qyx6sL0ZmHzwH5jIUR5S1xwqWhSAjlOUHLSg2tYfHx0dn3UV2koY9QsKEQIDAQABAoIBAUs9S728rej+eajR7WJoNKA8pNpg3nSj6Y4sAYNw64dun7uEmwO51gleBt0Cf23OaFNaf5KQ7QrNWbeBTs/uHTcHcV4dvw7yxA6SmsPdJTB+3i1M/W4vUIFPI9q930YxA+IA9p1bQwrWb42FRWwhgvX9FyE4rjkfAu0UNbjQHoDAxNFHHW2OZm9DFtZE8Y3qFFLXjnwl2acFncexDbY0A9vVR+ldpTruz7LQXRmAhozXmVnRtzMlDWDB0hjUQJYIAuue6tTHuD6VcxGLKYUgfB4AZ8IkvD2cbky38omll3KvaPbtOJFGNsBaqt0PVrZv//iZQHgIZe2roKNGBpUNA/7BAoGBD0H/eeG8KoFjLyqLLlBDndHPb3aURJGqf/rFuooK29NBn5n30cftFv7mXc+RA9/SBbIrX5+0p6Nez4SuJLYfVHBzDF9YOVzQbDUWZZWIUtR0yBSl2WAFEET7jyofzXTKOCo2eFrnWj5a2Q0xEFj11f7q83pbdQ8HvbUdi+roaRCtAoGBDM5hrDc4CI8yR6La9fAtA4bkQ/SxiDwfh/Xnjg0eSiVIs5GeGCAJEIeNoZE7n9BQHhH5m0HyjHMnzMfLyexhW6/xHAktEvcEWZMBBIBTbXsGn/f4yKiyfLCsdoLtQIBBQTpYAXwbqVjE+L6xgK/noFdDV17XZcYbPk6xr+f6Hnd1AoGBCQi2z9/Mng5GP+MczatQnd1gyUqYt5DYNzawZKbfjxEixfFQPrH1u6vpkpoX7wdTP3QjIldZi/i7ZnvU8H+1RTXfqO+7ORuvfKJiRHupYAHTs7QmDvM/jEaL/FSgx/Hi2iaEYfbRDSnmeKXK6zcBOFfbnZZRGJpxpu3aNMI+IhdxAoGBC2RplWeGDG8+3mVs/k6L7NBKLn32lOhPcIb8V+0pnfIvC7el+XY+OhssjqeBcDlDnIyHDWwMVo92v4CZtOb48TTCfBtZor5mez0AMb3q+cDw8swI4JDaP3x33/G3F6NA6cL6WU/L18nlaBdUFtPlbUlT2dzAJ4Sl5bbh8UefxQylAoGBAKP0QllPVH3cAKcplu/vTm3Brw6cejhwUX21qBspHJDQcjbqQMqI4xdcY7oYwBGazgPKnBOgiRqSg4018dcJySL5tHneGuTXHVfp+4FznlOQKxRg7I6e/KUOzRSsLy49KlGs9OmuACe0MOTboDIn00mzUnxdmk4qsq34KaqJ4w5G',
          'sp_name_id_format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
          'map_users' => 0,
          'security_logout_requests_sign' => 0,
          'security_assertions_encrypt' => 0,
          'security_authn_requests_sign' => 0,
          'security_messages_sign' => NULL,
          'security_request_authn_context' => 1,
          'strict' => 1,
        ])
        ->save();
    }
  }

  /**
   * Tests viewing a configuration entity as JSON via the API.
   */
  public function testViewConfigEntityAsJson() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/structure/contact');
    $page->clickLink('View JSON');
    $assert_session->statusCodeEquals(200);

    $this->drupalGet('/admin/structure/media');
    $page->clickLink('View JSON');
    $assert_session->statusCodeEquals(200);
  }

}
