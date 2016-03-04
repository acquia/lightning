@lightning @api @workflow
Feature: Responsibility-based user roles for managing workflow

  @beta4
  Scenario: Users with the Content Creator role can embargo individual nodes and transition between Draft and Needs Review moderation states
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/content_creator"
    # Permissions added by lightning_workflow #8001
    Then the content_creator role should have permission to:
    """
    create node_embargo scheduled updates
    delete own node_embargo scheduled updates
    edit own node_embargo scheduled updates
    view any unpublished content
    view latest version
    use published_draft transition
    use draft_draft transition
    use needs_review_needs_review transition
    use draft_needs_review transition
    """
