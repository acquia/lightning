[![Build Status](https://travis-ci.org/acquia/lightning.svg?branch=8.x-1.x)](https://travis-ci.org/acquia/lightning)

# Drupal Lightning
Lightning's mission is to enable developers to create great authoring
experiences and empower editorial teams.

You'll notice that Lightning appears very sparse out of the box. This is by
design. We want to empower editorial teams and enable developers to jump-start
their site builds. That means that a developer should never have to undo
something that Lightning has done. So we started with a blank slate and
justified each addition from there.

Through custom modules and configuration, Lightning aims to target four
functional areas:

## Media
The current version of media includes the following functionality:

* A preconfigured Text Format (Rich Text) with CKEditor WYSIWYG.
* A media button (indicated by a star -- for now) within the WYSIWYG that
  launches a custom media widget.
* The ability to place media into the text area and have it fully embedded as it
  will appear in the live entity. The following media types are currently
  supported:
  * Tweets
  * Instagram posts
  * Videos (YouTube and Vimeo supported out of the box)
  * Images
* Drag-and-drop image uploads
* Ability to create new media through the media library (/media/add)
* Ability to embed tweets, Instagrams, and YouTube/Vimeo videos directly into
  CKEditor by pasting the video URL

## Layout
Lightning includes the Panelizer module, which allows you to configure the
layout of any content type using a drag-and-drop interface (Panels IPE).
Lightning also includes a Landing Page content type for you to create
landing pages with their own one-off layouts and content.

Any content type that uses Panelizer will allow you to set up default layouts
for each view mode of that content type, which you can choose from (or override
on a one-off basis) for individual pieces of content.

Eight layouts are provided out of the box by Panels. You can create your own
layouts (see the [Layout Plugin](https://www.drupal.org/project/layout_plugin)
module) or install a contributed library of layouts like
[Radix Layouts](https://www.drupal.org/project/radix_layouts).

## Workflow
Lightning includes tools for building organization-specific content workflows.
Out of the box, Lightning gives you the ability to manage content in one of four
workflow states (draft, needs review, published, and archived). You can create
as many additional states as you like and define transitions between them. It's
also possible to schedule content (either a single node or many at once) to be
transitioned between states at a specific future date and time.

## Preview (Experimental)
The Workspace Preview System (WPS) gives site builders, editors, authors, and
reviews the ability to send collections of content through an editorial workflow
and preview that content within the context of the current live site. WPS is a
collection of contributed Drupal modules with additional configuration UX
improvements that all just works out of the box.

## Installing Lightning
The preferred way to install Lightning is using our
[Composer-based project template][template]. It's easy!

If you don't want to use Composer, you can install Lightning the traditional way
by downloading a tarball from our
[drupal.org project page](https://www.drupal.org/project/lightning).

## Project Roadmap
The roadmap is subject to change, but our projected schedule is:

* QTR3/4, 2016
  * Point in time preview
  * Personalization
  * Remote replication (store workspaces on external apps)
  * Search API integration

You can also look for general enhancements along the way. Please use the
[Lightning issue queue][issue_queue] for latest information and to request
features or bug fixes.

## Resources
You can find general best practices documentation inside the `help` directory of
each Lightning "base" module. Integration with the
[Advanced Help](https://www.drupal.org/project/advanced_help) module is planned.

Please file issues in our [drupal.org issue queue][issue_queue].

## Running Tests
These instructions assume you have used Composer to install Lightning.

### Behat
    $ cd MYPROJECT/docroot/profiles/lightning
    $ /path/to/MYPROJECT/bin/behat

If necessary, edit behat.local.yml to match your environment. Generally you
will not need to do this.

## Known Issues

### Media

* If you upload an image into an image field using the new image browser, you
  can set the image's alt text at upload time, but that text will not be
  replicated to the image field. This is due to a limitation of Entity Browser's
  API.

### Preview

* This functionality relies on Multiversion, which:
  * Does not yet have a stable release
  * Modifies internal data structures
  * Leaves permanent changes in the database after being uninstalled
  * Introduces the concept of a trash bin. Deleted content is hidden, but not
    immediately removed from the database anymore. It needs to be deleted and
    then "purged" to be completely wiped from the database. However, the user
    interface to purge deleted content is provided by the Trash module, which
    is not yet ready. This makes it impossible to truly delete content from the
    UI. (Lightning provides a shim for this, but it only works for nodes at this
    time.)
* There are several scenarios where URL aliases might produce unexpected
  results, including:
  * Pathauto is enabled, but rules are not configured for all content types
  * Overriding aliases generated by Pathauto
  * Aliases for any non-node entities
* Blocks on the block listing page(s) are not properly filtered by workspace
  under certain circumstances.
* The Workspace listing page will display a PHP warning caused by the Workspace
  module which is effectively harmless but may look alarming.
* The Author user-reference relationship that is implicit with all Node Entities
  is lost when replicating from workspace to workspace. So if UserA creates
  NodeB on the Live workspace, and that node is pulled to the Stage workspace,
  the Stage workspace will be unaware of the author and will set the author to
  Anonymous. Furthermore, if an edit is then made to NodeB on the Stage
  workspace, and that edit is pushed back to Live, NodeB on the Live workspace
  will also lose its author.
* There is no way yet to properly resolve conflicts between workspaces. Users
  can delete conflicting entities from one of the two workspaces to remove
  conflicts, but there is no interface yet for picking the winner and keeping
  both versions.

[issue_queue]: https://www.drupal.org/project/issues/lightning "Lightning Issue Queue"
[template]: https://github.com/acquia/lightning-project "Composer-based project template"
[d.o_semver]: https://www.drupal.org/node/1612910
[lightning_composer_project]: https://github.com/acquia/lightning-project
