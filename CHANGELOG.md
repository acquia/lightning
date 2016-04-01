## Release Candidate 1 (RC1)
* You can finally create a Lightning-based project entirely with Composer!
  See the [installer project page](https://github.com/acquia/lightning-project)
  for more information. (Issue #2693829)
* It's now possible for Quick Edit to edit content blocks placed in a landing
  page using Panels IPE. (Issue #2692391)
* A Lightning Media unit test was missing a @group annotation. (Issue #2695625)
* Lightning now adds a warning gate to ```drush update```, since Drush's code
  update mechanisms can destroy a working site. (Issue #2694367)
* All contributed modules included with Lightning have been moved into a single
  contrib directory -- no more subfolders for layout modules, media modules, etc.
  (Issue #2692229)
* We've adopted a versioning policy that should help bridge the gap between
  drupal.org (which does not support semantic versioning yet) and Composer. See
  VERSIONS.md for more information. (Issue #2693897)

## Beta 5
* Lightning now automatically provides responsibility-based roles for
  assigning permissions to users. All content types receive their own
  "Creator" and "Reviewer" roles, the main difference being that creators
  do not have the power to publish content. There is a basic "Media Creator"
  role for creating new media assets, and a more powerful "Media Manager"
  role for administrative purposes. Content type roles have limited power
  to control the layout of individual pieces of content, but there is
  a "Layout Manager" role for setting the default layouts for content types.
  (Issue #2670614)
* Lightning no longer provides the option to install demo content.
  (Issue #2673258)
* Lightning enforces a less confusing, more usable user experience when
  creating moderated content. (Issue #2671238)
* When embedding a media asset in CKEditor, authors will now receive additional
  options to control the display of the asset. (Issue #2677926)
* Previously, new media types would not appear in the CKEditor media library.
  This is now fixed. (Issue #2688467)
* Scheduling multiple pieces of content to be published was previously broken.
  (Issue #2690015)
* Developers: Lightning's functional tests are now part of the Lightning
  profile, not their own sub-package. (Issue #2681359)
* Due to a bizarre issue in Panelizer, one could not save changes to landing
  pages using Panels IPE until the default layout was re-saved. (Issue #2678900).

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
