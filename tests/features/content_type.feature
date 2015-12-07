@lightning @content_types
  Feature: Lightning Content Types
    Makes sure that the article content type was created during installation.

@api
  Scenario: Make sure that the content types provided by Lightning at installation are present.
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/structure/types"
    Then I should see "Article"

