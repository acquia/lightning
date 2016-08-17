@lightning @media @api
Feature: Instagram media assets
  A media asset representing an Instagram post.

  @javascript
  Scenario: Creating an Instagram media entity
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/instagram"
    And I enter '<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-version="6"><div> <div> <div></div></div> <p> <a href="https://www.instagram.com/p/-Ph6HzvrEl/" target="_blank">Starting to celebrate a little early! We can&#39;t wait to #celebr8d8 #drupal8 #acquia</a></p> <p>A photo posted by Hannah Corey (@hannahcorey) on <time datetime="2015-11-18T22:18:21+00:00">Nov 18, 2015 at 2:18pm PST</time></p></div></blockquote><script async defer src="//platform.instagram.com/en_US/embeds.js"></script>' for "Instagram post"
    And I wait for AJAX to finish
    And I enter "Foo" for "Media name"
    And I press "Save and publish"
    Then I should be visiting a media entity
    And I should see "Foo"
    And I queue the latest media entity for deletion

  Scenario: Viewing an Instagram post as an anonymous user
    Given instagram media from embed code:
    """
    <blockquote class="instagram-media" data-instgrm-captioned data-instgrm-version="6"><div> <div> <div></div></div> <p> <a href="https://www.instagram.com/p/-Ph6HzvrEl/" target="_blank">Starting to celebrate a little early! We can&#39;t wait to #celebr8d8 #drupal8 #acquia</a></p> <p>A photo posted by Hannah Corey (@hannahcorey) on <time datetime="2015-11-18T22:18:21+00:00">Nov 18, 2015 at 2:18pm PST</time></p></div></blockquote><script async defer src="//platform.instagram.com/en_US/embeds.js"></script>
    """
    And I am an anonymous user
    When I visit a media entity of type instagram
    Then I should get a 200 HTTP response
