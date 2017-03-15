@lightning @media @api
Feature: Media assets based on embed codes

  @javascript
  Scenario Outline: Creating a tweet
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

  Scenario Outline: Viewing a tweet as an anonymous user
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
