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

This is an early release of a subset of the planned features for Media and
Layout.

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

We hope to make the following enhancements to the Media Feature soon after
releasing Beta 1:

* Ability to float media left or right, display inline, or display block with no
  float
* Ability to resize media and crop image media
* Embed code paste area within the media widget
* Support for audio assets (SoundCloud, etc.)

### Layout

Lightning provides the ability to create a landing page through a custom form
wizard that uses Page Manager and Panels + Panels IPE. Two layouts are provided
by defaults (via Panels) and others can be added through the Layout Plugin or
contrib such as Radix Layouts.

## Project Roadmap

The roadmap is subject to change, but our projected schedule is:

* End of QTR 4: Beta1 with Media and Layout
* Early February: Beta2 with Workflows and Scheduling
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

## Quick install

Run `./build.sh` to use drush make to build the distribution and copy the most up to date versions of the lightning modules into the site.

This will create a `/docroot`.

### Setup Drupal

You will then need to perform a normal Drupal 8 install.

First, create a database.

#### Option A: Use the script to create necessary files

`./build-local-settings.php`

The following default DB connection is added to `docroot/sites/default/settings.local.php` and can be updates as needed.

```
$databases['default']['default'] = [
 'driver' => 'mysql',
 'database' => 'lightning',
 'username' => 'root',
 'password' => 'password',
 'host' => 'localhost',
 'collation' => 'utf8mb4_general_ci',
];
```

#### Option B: Manually install files

Create `docroot/sites/default/settings.php` and `docroot/sites/default/services.yml`.

Create a `docroot/sites/default/files` directory. Set the permissions on this directory so that the webserver can read it.

For example, `chmod -R 777 docroot/sites/default/files`

### Install site

#### Option A: Drush

`cd docroot`  
`drush si lightning`

#### Option B: Manual

Open the browser to `[your-local-site]/install.php`
