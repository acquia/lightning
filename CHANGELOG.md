## Beta 4
* Updates Drupal Core to 8.0.5.

## Beta 3
* Panelizer is now included with Lightning as a dependency of the Layout
  component.
* The Layout component now includes a new content type called Landing Page -- a
  simple node type whose layout is controlled out-of-the-box by Panelizer. This
  replaces the previous method of creating Landing Pages view Page Manager. As a
  result, we have removed the Create Landing Page shortcut since you can create
  Landing Pages from the same place you create other nodes.
* Fixed a bug where the Body field was not present on the Basic custom block
  type.
* Patched Panels and Workbench Moderation to make them play nice together. At
  this time, it is possible to use the Panels in-place editor ONLY on the latest
  revision of panelized content. (Put another way -- if Quick Edit does not show
  up, neither will Panels IPE.)
* Updated several modules to their latest tagged versions.
* The Media component has been split into several smaller features -- one each
  for image, Twitter, video, and Instagram support. All depend on the main
  Lightning Media module and are enabled by default.
* All Lightning components are now packaged using Features, and Features is
  included and enabled in Lightning by default. Features UI is included, but not
  installed.

## Beta 2
* Introduced the Lightning Workflow module, which provides tools for workflow
  control based on Workbench Moderation and Scheduled Updates. You can
  transition content between Draft, Needs Review, Published, and Archived
  states, or schedule content to be transitioned later.
* You can view a very basic, human-readable report of the moderation history for
  any node that supports workflow states.
* The media library plugin for CKEditor now supports the use of embed codes to
  add tweets, Instagram posts, and YouTube videos to your media library
  on-the-fly.
* It's now possible to filter your CKEditor media library by media type.

## Beta 1
This is a tag-only release. No functional changes from Alpha 5.

## Alpha 5
* Added JS "niceness" to landing page creation form
* Added dependency injection to LandingPageForm for easier testing
* Added support for Selenium testing
* Added ability to choose layout when creating landing page
* Added ability to set page title when creating a landing page
* Added tests for media library widget
* Removed Radix layouts
