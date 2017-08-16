## 2.1.7
* Security updated Drupal core to 8.3.7.
* Updated Entity Browser to 1.1.
* Lightning has a new top-level component called Content API. This component is
  installed by default and exposes all entities in your site in the
  machine-consumable JSON API format. This makes Lightning friendlier to
  decoupled applications, and allows it to be used as a backend for such. As
  part of this feature, Lightning now includes the JSON API, OpenAPI, and
  Simple OAuth modules, with basic default configuration.
  (GitHub #423, #421, #424, #433, and Issue #2896267)
* Lightning now supports bringing in front-end JavaScript libraries with
  Composer, via Asset Packagist. To take advantage of this in your
  Composer-based Lightning project, follow the instructions at
  http://lightning.acquia.com/blog/round-your-front-end-javascript-libraries-composer
  (GitHub #431)
* Lightning Core no longer has a hard dependency on the Metatag or Menu UI
  modules. (GitHub #418, #420 and #427)
* Lightning no longer has an implicit hard dependency on the Bartik or Seven
  themes. (Issue #2899017)
* Page Manager is no longer required by Lightning Layout. It is still shipped
  with Lightning, but is not a dependency and will be removed from Lightning in
  the next release. If you are using Page Manager, you must explicity include
  it as a dependency of your project. Otherwise, you should uninstall it as
  soon as possible. (GitHub #410)
* Quick Edit is no longer visible on published content when Lightning Workflow
  is enabled, because Quick Edit does not deal properly with forward revisions.
  In Lightning, you will only be able to use Quick Edit on unpublished drafts.
  (Issue #2894874)
* Split scheduled update functionality into a sub-component of Lightning
  Workflow, installed by default on new Lightning sites. (Issue #2893542)
* Panels was updated to version 4.2. (GitHub #409)
* Removed unnecessary lightning_core_entity_load(). (GitHub #406)

## 2.1.6
* Lightning now provides a Display Plugin for images embedded via CKEditor that
  allows editors to select an image style, alt text, and other settings each
  time an image is embedded. (Issue #2784699) 
* You can now select and insert media items from a single-cardinality media
  browser with a double-click. (Issue #2888535)
* Added documentation about the known incompatibility between Workbench
  Moderation and Content Moderation. (Issue #2869257)
* Fixed a bug where Lightning Core might try to alter the value of a
  non-existent array key for unit tests. (GitHub #394)

## 2.1.5
* Drupal core was security updated to 8.3.4.
* Layout Plugin is no longer included with Lightning. (Issue #2873728)
* Lightning is now pinned to the 3.2.x line of the Drupal Extension for Behat
  due to an incompatibility between the latest versions of it and Behat. See
  https://github.com/jhedstrom/drupalextension/issues/386 for more information
  (GitHub #389)
* Lightning now tests its bundled configuration for proper conformance to
  configuration schema. (GitHub #383 and #388)

## 2.1.4
* Implemented UX improvements for media reference fields using Lightning's
  media browser -- the maximum number of items you can select will be displayed
  above the field. (GitHub #363)
* Fixed a bug where the media browser's upload widget, when used with an entity
  reference field, would not respect the media bundles that the field could
  reference. (GitHub #370)
* Fixed a bug where content types that do not use Workbench Moderation would not
  display their "Create new revision" checkbox. (Issue #2876698)
* All of the entity view and form displays bundled with Lightning now include
  region information. (GitHub #366)
* Patched Drupal core to suppress non-actionable warnings about expected
  behavior. (Issue #2878149 and GitHub #372)
* Various default configuration bundled with Lightning Media was updated.
  (GitHub #365)
* Hid the "Entity View" block provided by CTools from Panels IPE, since it was
  not compatible anyway. (Issue #2834173)
* Acquia Connector, Media Entity Instagram, Metatag, and Search API were
  updated to their latest versions. (GitHub #376)
* Patched Panels to include three UI/UX improvements. (Issue #2884163)
* Implemented a system to continuously generate configuration snapshots so that
  config schema changes made by modules can be propagated into Lightning's
  bundled default configuration. (GitHub #368)
* Implemented a safeguard to ensure that dependencies which Lightning is
  patching are always be pinned to a specific version. (GitHub #361)

## 2.1.3
* Created new Drupal Console commands that generate and customize behat.yml
  configuration files for functional testing. (Issue #2812775 and GitHub #350)
* Fixed a bug where the media library filter was hidden when the contextual
  filter value was "all". (GitHub #352 and #354)
* Updated Panels, Panelizer, Page Manage, and CTools to stable releases.
  (Issue #2874521)
* Fixed a bug where Lightning could, under certain circumstances, break Drupal's
  configuration sync functionality. Now, when a config sync is in progress,
  Lightning will avoid making any changes to active configuration.
  (Issue #2870864)

## 2.1.2
* Updated Entity Browser to 1.0.0 and pinned it to that release to ensure patch
  applies.

## 2.1.1
* Panels, Panelizer and Page Manager have been upgraded to their new (stable!)
  8.x-4.x releases. These releases use the experimental Layout Discovery module
  in Drupal core, and will turn off Layout Plugin upon installation. Layout
  Discovery is incompatible with Layout Plugin, so do NOT install Layout Plugin
  once the upgrade is complete. (Issue #2870521)
* The media browser will now be filtered conditionally when used with media
  reference fields, depending on which media types the field can reference.
  (Issue #2869240)
* Implemented an API for bulk entity creation. A UI for bulk upload media items
  was implemented, then pulled due to packaging issues. A patch containing that
  UI is available at
  https://www.drupal.org/node/2672038#comment-12044162, and will be merged back
  into Lightning when the packaging problems are fixed. (Issue #2870740)
* Fixed a bug where Lightning Workflow would wrongly interfere with the Save
  button when creating or editing unmoderated content types. (Issue #2867465)
* Fixed a bug where uninstalling Field UI would break Lightning due to an
  implicit dependency. (GitHub #340 and #327)
* Search API was updated to its latest release candidate. (GitHub #334)
* Listed third-party Lightning Media integrations in the README. (GitHub #339
  and #346)
* Lightning now uses short array syntax in all of its code. (Issue #2867638)

## 2.1.0
* Lightning has been updated to run on and now requires Drupal Core 8.3.x.
* Created a new Experimental branch and moved all experimental components out of
  the stable branch. (Issue #2862124)
* Removed all code tagged as @deprecated.
* Fixed a bug introduced in 2.0.6 that prevented images from being removed once
  added to a media bundle image field. (Issue #2865794)
* Fixed a bug where, under certain circumstances, Lightning Media Image might
  attempt to setup roles before Lightning Roles was enabled. (GH Issue #318)
* Updated the core inherited profiles patch which will now take into
  consideration whether an installed extension is a base or parent profile when
  building dependency trees for the Configuration Importer. (GH Issue #317)
* Fixed a bug introduced by the beta5 release of Search API and patched a
  separate bug the update path to the same release.

## 2.0.6
* All user roles provided by Lightning's various components have been split out
  into a new sub-component of Lightning Core, called Lightning Roles. This
  sub-component is installed with Lightning by default, but you can disable it
  in a sub-profile. If it's disabled, Lightning will not create or install any
  user roles. (Issue #2855724)
* New entity reference fields that reference media items will now use
  Lightning's media browser by default. This change only applies to new entity
  reference fields; existing fields are left alone. (GitHub #298)
* A preview of embed code-based media items will now be displayed when adding
  or editing them outside of the media browser. (Issue #2825935)
* Fixed a bug where the file upload widget used by Lightning's media and image
  browsers would wrongly assume that all media bundles use a source field.
  (Issue #2861292)
* Drush, which is included with Lightning as a dev dependency, was patched to
  fix a problem where dependencies of parent profiles could not be uninstalled.
  (GitHub #311)
* The lightning.config_helper service is deprecated and replaced by a new
  facade for manipulating a module's default configuration. (GitHub #303)
* Many tags have been added to Lightning's Behat test suite to make it easier
  to isolate and run (or skip) individual tests. (Issue #2862119)
* The internal Lightning Dev module now generates a special behat.yml file in
  Drupal's public files directory, allowing any module to expose its own Behat
  test suite by including a tests/behat.yml file. (GitHub #299)

## 2.0.5
* Lightning can now be used as a base profile and contains a script to generate
  a sub-profile. (Issue #2855793)

## 2.0.4
* The media browser now allows you to select more than one item for multi-value
  fields. (Issue #2829444)
* Scheduled updates now clearly display what is scheduled to happen and when,
  and multiple updates can be created for basic pages. (Issue #2688411)
* Fixed a bug where reverting the layout of a forward revision of a landing page
  also reverted the layout of the published version. (Issue #2754649) 
* Fixed a bug where Lightning Media failed to validate file size and dimension
  constraints. (Issue #2796683)
* The *.features.yml files were removed from our older features that had them.
  (Issue #2846724)
* Lightning will no longer install Contact and Contact storage if you exclude
  Lightning Contact Form from being installed. (Issue #2854662)
* Fixed a bug in Lightning's Behat configuration that prevented custom paths
  from being used for files. (GitHub #278)
* Lightning will no longer install Search API if you exclude Lightning Search
  from being installed. (Issue #2855075)
* Quick Edit now works with forward revisions and content blocks placed via the
  in-place editor. (Issue #2847467)
* Added a configuration form to Lightning Layout that allows you to choose which
  entity types can be embedded as blocks. (Issue #2851583)
* Fixed a bug where image style generation failed for image files with uppercase
  extensions. (Issue #2857694)
* Content reviewer roles now have permission to view moderation states.
  (GitHub #287, Issue #2825934, and Issue #2825928)
* Fixed a bug where unmoderated content types would not show up in the Content
  view. (Issue #2858566)

## 2.0.3
* Added the Entity Blocks module, which provides block types that can display
  any renderable entity without needing a context. This allows content editors
  to easily embed existing content in a landing page using the in-place editor.
  (Issue #2667896)
* Lightning now includes Search API with an out-of-the-box site search page, a
  database backend, and sane default configuration. (Issue #2674180)
* Added help text to the edit form for workspaces that documents how to push a
  workspace's content live. (Issue #2835105)
* Fixed a bug where Lightning Media failed to declare its dependency on
  CKEditor. (Issue #2847011)
* Lightning Workflow now includes a column on the content list page that
  indicates if a piece of content has unpublished edits (a.k.a forward
  revisions). (Issue #2837788)
* Fixed a bug that could cause an exception when translating a field.
  (Issue #2841172)
* It's now possible to display taxonomy terms using Panelizer. (Issue #2664574)

## 2.0.2
* Workbench Moderation was updated to 8.x-1.2. (Issue #2838896)
* All info files supplied with Lightning's components now have consistent
  version numbers. (Issue #2839593)
* Lightning now installs the Diff module by default. (Issue #2762325)
* We now verify that all Lightning YAML files are compatible with the strict
  PECL parser.
* Lightning now installs the core Contact module by default, and includes
  and installs the Contact Storage contrib module to provide basic form
  building and submission management functionality -- a pared-down Webform
  that should suffice for many simple use cases. (Issue #2666424)
* Fixed a problem where Lightning Extension's subcontexts for Drupal Extension
  would not be autoloaded by Behat. (Issue #2836258)
* Fixed a fatal error when trying to display a description for a view mode
  that does not exist. (GitHub #254)
* All titles, links and headings were changed to sentence case. (GitHub #252)

## 2.0.1
* Replaced test files with generic Lightning logos. (Issue #2836442)

##  2.0.0
* Switched to the official Drupal.org packagist.

## 1.14
* Added Panelizer support for view mode descriptions. (Issue #2828638)
* Tarball releases are now built with contrib versions from drupal.org and not
  git (Issue #2827227)
* Updated Panelizer patch to fix a problem where it did not properly define its
  dependency on Field UI (GitHub #226)
* Improved the author-facing Panelizer experience by implementing "Internal"
  View Modes for which Panelizer is always disabled. (GitHub #194 & 223)
* Fixed a bug where Lightning assumptions + certain contrib modules would cause
  an infinite loop. (Issue #2831550)
* Included a script that will convert your project's root composer.json file to
  use the official Drupal.org packagist and update your project to Lightning
  2.0.0 which also uses the official Packagist. (See: [Lightning Packagist Switch](http://lightning.acquia.com/blog/packagist-switch))
* Lightning no longer provides default content for the Shortcut menu.
  (Issue #2834874)
* Made it possible for modules and themes to pass CSS to a CKEditor instance.
  (Issue #2729377)

## 1.13
* Tests now ensure that composer.lock is kept up to date. (GitHub #132)
* The page title block is now properly placed in the Seven theme. (GitHub #190)
* Twitter media entities can now be configured to automatically generate
  thumbnails for textual tweets. (GitHub #203)
* CI now uses database snapshots for update tests. (GitHub #201)
* Fix Multiversion regression of node revisions list. (Issue #2824633
  and #2825477)
* Display modes and user roles can now have associated descriptions.
  (GitHub #195)
* Improved usability of the Panelizer interface. (Issue #2826071)
* Lightning extender can now be used to exclude submodules of Lightning
  extensions. (GitHub #220)
* Updated all dependencies and core to their latest releases. (GitHub #219)

## 1.12
* Drupal core, and several contributed dependencies, were updated to their
  latest stable releases.
* Locked workflow states will now be clearly denoted with a lock icon when
  editing a workspace. (GitHub #199)
* The list of workspaces will no longer display a Status column. (GitHub #200
  and #184)
* Explained the Lightning Extender in README. (GitHub #198)
* Node authorship is now preserved during replication between workspaces.
  (GitHub #191, Issue #2817231)
* File entities are now opted out of Multiversion control (i.e., all files
  will always exist in all workspaces.) (GitHub #197)

## 1.11
* Fixed a dependency problem that was preventing Lightning from being installed
  via Composer. (Issue #2699121)
* Patched a core bug that could in certain circumstances result in file copies
  failing during installation. (GitHub #179, Issue #2782239 and #2818031)
* Lightning Preview is now compatible with Pathauto. (Issue #2817253)

## 1.10
* Drupal core updated to 8.2.1.
* Introduced Lightning Preview module and Workspace Preview System.

## 1.06
* Drupal core updated to 8.2.0!
* Previously, the Metatag module could break Drush. This is now patched
  (see https://www.drupal.org/node/2786795), and Metatag has been updated
  to 8.x-1.0-beta10.
* Panels has been updated to 8.x-3.0-beta5 (security update).

## 1.05
* Drupal core was updated to 8.1.10.
* Several contrib dependencies were updated.

## 1.04
* drupal-composer/drupal-scaffold is now strictly a dev dependency
  for Lightning. (GitHub #142)
* All of Lightning's Behat tests now carry the @lightning tag.
  (#2771273)
* Pagination was not working in the media browser due to
  out-of-the-box misconfiguration. (#2783149)
* Added a sanity check when adding new Image fields. (#2781395)
* Most of the configuration previously provided as part of the
  Lightning install profile has been moved into Lightning Core.
  (#2773519)
* The "publish" and "unpublish" actions have been removed from the 
  administrative Content view, because they do not make sense with
  Workbench Moderation enabled. (#2705931)
* Fixed a regression caused by changes in Panelizer. (#2790699)
* Fixed a problem where installation could result in an error due to
  an invalid configuration dependency in the Basic Page node type
  included with Lightning Core. (#2795899)
* The Layout Manager role no longer has administrative capabilities
  by default. Fixing this also restored the ability to select the
  administrative role at Admin > Config > People. (#2792147, #2792989)

## 1.03
* Lightning now includes an image browser for uploading images to and
  selecting images from your media library. It is automatically used
  for all new image fields by default, to give your users a much nicer
  out-of-the-box experience of dealing with image assets. The image
  browser is NOT automatically added to any existing image fields, but
  they can be manually changed to use it. (#2767213)
* Several other modules, including Entity Browser, were updated as well.
  Note that Entity Browser introduced several backwards-incompatible API
  changes, so if you have custom Entity Browser code you may need to
  update. Be safe and back up your database before running updating to
  this version of Lightning. (#2778437)
* Views Infinite Scroll was updated to 8.x-1.2. (#2773811)
* A sanity check was added to prevent fatal errors when preparing extra
  fields for media asset previews. (#2759825)
* The Lightning Extender will now search for lightning.extend.yml in sites/all
  as well as your site's individual directory. This means it's now possible for
  every site in a multisite Lightning installation to use the same extender
  configuration. (#2766337)
* Fixed a problem where the media browser would not appear when editing a node
  with an embedded tweet. (#2768849)
* The Lightning Extender's redirection feature was not working and would always
  send users to a "Drupal is already installed" error page. (#2775425)
* Fixed a PHP notice arising from the media asset preview handler. (PR #140)

## 1.02
* Updated core to 8.1.7 (Security Release) and all contrib modules to latest 
  available releases.
* It's now possible to define which Lightning extensions will be enabled by
  listing them in ```lightning.extend.yml```. (#2765627)
* Extender::getRedirect() now checks to see if key exists before reading value
  to prevent a PHP notice from being displayed on install. (#276446)
* Fixed a bug where embedded tweets did not appear in CKEditor. (#2764909)
* Applied a patch to suppress metatag messages during install. (#2765137)

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
