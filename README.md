# Drupal Lightning

Lightning's mission is to enable developers to create great authoring
experiences and empower editorial teams. Through custom modules and
configuration, Lightning aims to target four functional areas:

* Layout
* Preview
* Media
* Workflow

## Current version

Alpha1 is an early release of a subset of the planned features for Media. It is
not stable and there will be no upgrade path. There is also very limited support
for creating landing pages through Page Manager and Panels IPE.

**Lightning Alpha1 is a Media-only release. The other functional areas will be
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

