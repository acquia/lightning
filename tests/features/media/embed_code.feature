@lightning @media @api
Feature: Media assets based on embed codes

  @twitter @instagram @video @javascript @ecf865ce
  Scenario Outline: Creating a media asset from an embed code
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/<bundle>"
    And I enter "<embed_code>" for "<source_field>"
    And I wait for AJAX to finish
    And I enter "<title>" for "Media name"
    And I press "Save and publish"
    Then I should be visiting a media entity
    And I should see "<title>"
    And I queue the latest media entity for deletion

    Examples:
      | bundle    | embed_code                                        | title                            | source_field   |
      | tweet     | https://twitter.com/chx/status/493538461761028096 | Foobaz                           | Tweet          |
      | instagram | https://www.instagram.com/p/lV3WqOoNDD            | Foo                              | Instagram post |
      | video     | https://www.youtube.com/watch?v=zQ1_IbFFbzA       | The Pill Scene                   | Video URL      |
      | video     | https://vimeo.com/14782834                        | Cache Rules Everything Around Me | Video URL      |

  @twitter @instagram @video @0bc72722
  Scenario Outline: Viewing a media asset an anonymous user
    Given <bundle> media from embed code:
    """
    <embed_code>
    """
    And I am an anonymous user
    When I visit a media entity of type <bundle>
    Then I should get a 200 HTTP response

    Examples:
      | bundle    | embed_code                                             |
      | tweet     | https://twitter.com/webchick/status/672110599497617408 |
      | instagram | https://www.instagram.com/p/lV3WqOoNDD                 |
      | video     | https://www.youtube.com/watch?v=ktCgVopf7D0            |

  @video @with-module:lightning_test @javascript @c74eadd0
  Scenario: Clearing an image field on a media item
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/video"
    And I enter "Foobaz" for "Media name"
    And I enter "https://www.youtube.com/watch?v=z9qY4VUZzcY" for "Video URL"
    And I wait for AJAX to finish
    And I attach the file "test.jpg" to "Image"
    And I wait for AJAX to finish
    And I press "Save and publish"
    And I click "Edit"
    And I press "field_image_0_remove_button"
    And I wait for AJAX to finish
    # Ensure that the widget has actually been cleared. This test was written
    # because the AJAX operation would fail due to a 500 error at the server,
    # which would prevent the widget from being cleared.
    Then I should not see a "field_image_0_remove_button" element
