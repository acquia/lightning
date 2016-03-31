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
  * YouTube videos
  * Images
* Drag-and-drop image uploads
* Ability to create new media through the media library (/media/add)
* Ability to embed tweets, Instagrams, and YouTube videos directly into CKEditor
  from an embed code

## Layout
Lightning includes the Panelizer module, which allows you to configure the
layout of any content type using a drag-and-drop interface (Panels IPE).
Lightning also includes a Landing Page content type for you to create
landing pages with their own one-off layouts and content.

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

## Installing Lightning
The preferred way to install Lightning is using our
[Composer-based project template][template]. It's easy!

If you don't want to use Composer, you can install Lightning the traditional way
by downloading a tarball from our
[drupal.org project page](https://www.drupal.org/project/lightning).

## Project Roadmap
The roadmap is subject to change, but our projected schedule is:

* April 2016:
  * Ability to set bundle-level layouts from the node type display page
* QTR2 2016
  * Workspace Preview System
  * Remote replication (store workspaces on external apps)
  * Search API integration
* Further
  * Point in time preview
  * Personalization

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

### Jasmine Media Tests
    $ cd MYPROJECT/docroot/profiles/lightning/modules/lightning_features/lightning_media/tests/js
    $ npm install && npm test

[issue_queue]: https://www.drupal.org/project/issues/lightning "Lightning Issue Queue"
[template]: https://github.com/acquia/lightning-project "Composer-based project template"
[d.o_semver]: https://www.drupal.org/node/1612910
[lightning_composer_project]: https://github.com/acquia/lightning-project
