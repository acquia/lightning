# Drupal Lightning

Lightning's mission is to enable developers to create great authoring
experiences and empower editorial teams. Through custom modules and
configuration, Lightning aims to target four functional areas:

* Layout
* Preview
* Media
* Workflow

You'll notice that Lightning appears very sparse out of the box. This is by
design. We want to empower editorial teams and enable developers to jump-start
their site builds. That means that a developer should never have to undo
something that Lightning has done. So we started with a blank slate and
justified each addition from there.

## Current version

Alpha3 is an early release of a subset of the planned features for Media. It is
not stable and there will be no upgrade path. There is also very limited support
for creating landing pages through Page Manager and Panels IPE.

**Lightning Alpha3 is a Media-only release. The other functional areas will be
released in subsequent Alphas or Betas.**

### Media

The current version of media includes the following functionality:

* A preconfigured Text Format (Rich Text) with CKEditor WYSIWYG.
* A media button (indicated by a star -- for now) within the WYSIWYG that
  launches a custom media widget.
* The ability to place media into the text area and have it fully embedded as it
  will appear in the live entity. The following media types are supported:
  * Tweets
  * Instagram Posts
  * YouTube Videos
  * Images
* Drag-and-drop image uploads
* Ability to create new media through the media library (/media/add)

#### Short-term media roadmap

We hope to make the following enhancements to the Media Feature prior to
releasing Beta 1:

* Ability to float media left or right, display inline, or display block with no
  float
* Ability to resize media and crop image media
* Embed code paste area within the media widget
* Support for audio assets (SoundCloud, etc.)

## Project Roadmap

The roadmap is subject to change, but our projected schedule is:

* 4th Week in December: Alpha3 containing basic Layout support
* End of QTR 4: Beta1 with Media and Layout
* Late January: Beta2 with Workflows and Scheduling
* Late February: Full Layout Support
* Late March: Tagged release

You can also look for general enhancements along the way like OOTB Pathauto with
sane defaults and preconfigured roles and permissions that we think the majority
of site builds will use.

## Running Tests

### Behat

    # Move the tests folder into docroot and switch into that folder.
    # From docroot:
    mv profiles/lightning/tests tests && cd tests

    # Copy the behat.local.example.yml to behat.local.yml and replace BASE_PATH
    # with the path to your local install.
    cp behat.local.example.yml

    # Install dependencies with Composer.
    composer install

    # Run the tests
    bin/behat --profile=dev

### Jasmine Media Tests

    # Requires Node.js and NPM.
    # From /profiles/lightning/modules/lightning_features/lightning_media/tests/js;
    npm install && npm test

