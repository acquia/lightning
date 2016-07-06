## 1.01
* Mega patch for Panels included binary files and would fail to apply in certain
  environments. (#2752375)
* Removed custom step definitions which are now included in mink. (#2718123)
* Video Entity thumbnails are now regenerated if the referenced video is
  updated. (#2752429)
* Fixed database update that would occur if updating directly from RC6 to 1.00.
  (GH #133)
* Fixed an issue where all media was displayed in the library, regardless of
  the value of "Save to Library". (#2757473)
* Fixed an issue that prevented users from selecting certain cached elements
  from the media library. (#2757481)
* Updated the front page view to use the [site:name] token instead of printing
  "Lightning". (#2757351)
* Provided a standard way to extend Lightning. (#2734507)
* Improved CI now automatically tests update path.
* Lightning Media no longer clears the render cache after entity operations.
  (#2759313)

## 1.00
* Fixed regressions in panelizer and added test coverage. (#2751225)
* Updated drupal scaffold to latest release. (#2751541)
* Fixed plugin lazy loading usort warnings. (#2699157)

## Release Candidate 7 (RC7)
* Drupal core's developer dependencies are now included with Lightning so that
  you can run standard Drupal tests. (Issues #2703009 and #2747953)
* Lightning now integrates Panelizer's new administrative UI, which allows the
  creation of default layouts for any view mode of any content type. (Issue
  #2678240)
* Formalized Lightning's logic for determining dependency version constraints.
  (Issue #2745949)
* Updates Drupal Core to 8.1.3

## Release Candidate 6 (RC6)
* Lightning Media no longer depends on or references the Lightning profile
  directly. [#2692419]
* Site Builders can now choose to opt out of the user roles that Lightning
  generates per content type. Visit `admin/config/system/lightning` to change
  this setting. [#2715517]
* Added build status to README.md. [#2737655]
* Updated all dependencies to latest releases. [#2737745]
* The dependency on Drush has been moved to `require-dev`. [#2716657]
* Lightning Media has been rewritten to take advantage of Entity Browser. [#2726889]
* Lightning Media now supports documents (txt, pdf, doc, and docx).

## Release Candidate 5 (RC5)
* The Media  Entity Embeddable Video module has been deprecated and is replaced
  by Video Embed Field in this release. (Issue #2700399)
* Lightning now ships with the stable release of Drush 9 (currently alpha1).
  This fixes dependency conflicts with the Lightning installer.

## Release Candidate 4 (RC4)
* Drupal core updated to 8.1.1.
* Contrib modules updated to latest releases.
* The CKEditor media library widget was completely refactored. (Issue #2713695)
* The Rich Text input format now allows BR tags. (Issue #2693793)
* Under some circumstances, the CKEditor media library widget would trigger an
  AJAX error. (Issue #2717403)
* Lightning Layout's README contained incorrect information. (Issue #2711975)
* Resolved a warning about Options in lightning_workflow_form_node_form_alter
  (Issue #2703077)

## Release Candidate 3 (RC3)
* Update core to 8.1.0
* Update all contrib modules to their latest releases
* Certain modules and Drupal Core will now automatically update to the latest
  non-breaking release as they become available for composer-based installs.
* 2702009 - Fixed PHP notice about undefined title index.
* 2695727 - Fixed an improperly namespaced hook function.
* 2708609 - Fixed an issue where Entity Embed did not respect the Media Library
  button implemented by Lightning Media.
* 2688427 - Implemented a fix in Entity Embed where captions for Media Entities
  did not properly escape HTML.
* 2695543 - Implemented a fix in Workbench Moderation where WBM Transition
  weight could be lost if the site has more than 20 transitions defined.

## Release Candidate 2 (RC2)
* Updated core to 8.0.6. We also lowered the specificity on core releases for
  Composer-based installs. Core will automatically update to the latest Patch
  Release when `composer update` is issued regardless of whether there is a new
  release of Lightning. This was already the case since Drupal Packagist
  automatically adds a tilde to the drupal/core, but it's now documented and
  we've added the tilde to our own composer.json file to avoid confusion.
* Updated the following dependencies:
  * CTools: alpha24 -> alpha25
  * Workbench Moderation: beta1 -> beta3
  * MetaTag: beta5 -> beta7
  * Acquia Connector: 8.x-1.0 -> 8.x-1.1
* Fixed an issue where the path to a JS library was duplicated and caused
  problems when JS aggregation was turned off. (Issue #2700685)
* Fixed an issue where users editing layouts with the IPE affected other users
  until the changes were saved. (Issue #2701433)

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
