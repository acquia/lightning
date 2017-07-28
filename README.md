[![Build Status](https://travis-ci.org/acquia/lightning.svg?branch=8.x-1.x)](https://travis-ci.org/acquia/lightning)

# Drupal Lightning
Lightning's mission is to enable developers to create great authoring
experiences and empower editorial teams.

You'll notice that Lightning appears very sparse out of the box. This is by
design. We want to empower editorial teams and enable developers to jump-start
their site builds. That means that a developer should never have to undo
something that Lightning has done. So we started with a blank slate and
justified each addition from there.

## Installing Lightning
The preferred way to install Lightning is using our
[Composer-based project template][template]. It's easy!

```
$ composer create-project acquia/lightning-project MY_PROJECT
```

If you don't want to use Composer, you can install Lightning the traditional way
by downloading a tarball from our
[drupal.org project page](https://www.drupal.org/project/lightning). (Please
note that the tarball does not contain any experimental features.)

You can customize your installation by creating a [sub-profile which uses
Lightning as its base profile][sub-profile documentation]. Lightning includes a
Drupal Console command (`lightning:subprofile`) which will generate a
sub-profile for you.

## What Lightning Does
Through custom modules and configuration, Lightning aims to target four
functional areas:

### Media
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

#### Extending Lightning Media (Contributed Modules)
Drupal community members have contributed several modules which integrate Lightning Media with additional third-party media services. These modules are not packaged with Lightning or maintained by Acquia, but they are stable and you can use them in your Lightning site:

  * [Facebook](https://www.drupal.org/project/lightning_media_facebook)
  * [Imgur](https://www.drupal.org/project/lightning_media_imgur)
  * [Flickr](https://www.drupal.org/project/lightning_media_flickr)
  * [500px](https://www.drupal.org/project/lightning_media_d500px)
  * [SoundCloud](https://www.drupal.org/project/lightning_media_soundcloud)
  * [Tumblr](https://www.drupal.org/project/lightning_media_tumblr)
  * [Spotify](https://www.drupal.org/project/lightning_media_spotify)
  * [Pinterest](https://www.drupal.org/project/lightning_media_pinterest)  

### Layout
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

### Workflow
Lightning includes tools for building organization-specific content workflows.
Out of the box, Lightning gives you the ability to manage content in one of four
workflow states (draft, needs review, published, and archived). You can create
as many additional states as you like and define transitions between them. It's
also possible to schedule content (either a single node or many at once) to be
transitioned between states at a specific future date and time.

### API-First
Lightning ships with several modules which, together, quickly set up Drupal to
deliver data to decoupled applications via a standardized API. By default,
Lightning installs the OpenAPI and JSON API modules, plus the Simple OAuth
module, as a toolkit for authentication, authorization, and delivery of data
to API consumers. Currently, Lightning includes no default configuration for
any of these modules, because it does not make any assumptions about how the
API data will be consumed, but we might add support for standard use cases as
they present themselves.

If you have PHP's OpenSSL extension enabled, Lightning will attempt to create
an asymmetric key pair for use with OAuth. You should generate a new key pair
before putting your site into production; instructions for that can be found
[here](https://www.drupal.org/project/simple_oauth).

## Project Roadmap
We publish sprint plans for each patch release. You can find a link to the
current one in [this meta-issue][meta_releases] on Drupal.org.

## Resources
You can find general best practices documentation inside the `help` directory of
each Lightning "base" module. Integration with the
[Advanced Help](https://www.drupal.org/project/advanced_help) module is planned.

Demonstration videos for each of our user stories can be found [here][demo_videos].

Please use the [Drupal.org issue queue][issue_queue] for latest information and
to request features or bug fixes.

## Running Tests
These instructions assume you have used Composer to install Lightning. Once you
have it up and running, follow these steps to execute all of Lightning's Behat
tests:

### Behat
    $ cd MYPROJECT
    $ ./bin/drupal behat:init http://YOUR.LIGHTNING.SITE --merge=../tests/behat.yml
    $ ./bin/drupal behat:include ../tests/features --with-subcontexts=../tests/features/bootstrap --with-subcontexts=../src/LightningExtension/Context
    $ ./bin/behat --config ./docroot/sites/default/files/behat.yml

If necessary, you can edit ```docroot/sites/default/files/behat.yml``` to match
your environment, but generally you will not need to do this.

## Known Issues

### Media
* If you upload an image into an image field using the new image browser, you
  can set the image's alt text at upload time, but that text will not be
  replicated to the image field. This is due to a limitation of Entity Browser's
  API.

### Workflow
* Lightning Workflow is based on Workbench Moderation, which is incompatible
  with the experimental Content Moderation module included with Drupal core
  8.3.0 and later and serves the same purpose as Workbench Moderation. We plan
  to seamlessly migrate Lightning Workflow to Content Moderation once it is
  stable, most likely in Drupal 8.4.0. But for now, installing Content
  Moderation alongside Lightning Workflow may have unpredictable and dangerous
  effects, and is best avoided.

[issue_queue]: https://www.drupal.org/project/issues/lightning "Lightning Issue Queue"
[meta_release]: https://www.drupal.org/node/2670686 "Lightning Meta Releases Issue"
[template]: https://github.com/acquia/lightning-project "Composer-based project template"
[d.o_semver]: https://www.drupal.org/node/1612910
[lightning_composer_project]: https://github.com/acquia/lightning-project
[demo_videos]: http://lightning.acquia.com/blog/lightning-user-stories-demonstrations "Lightning user story demonstration videos"
[sub-profile documentation]: https://github.com/acquia/lightning/wiki/Lightning-as-a-Base-Profile "Lightning sub-profile documentation"
