# Lightning
[![Travis build status](https://img.shields.io/travis/acquia/lightning/7.x-1.x.svg)](https://travis-ci.org/acquia/lightning) [![Scrutinizer code quality](https://img.shields.io/scrutinizer/g/acquia/lightning/7.x-1.x.svg)](https://scrutinizer-ci.com/g/acquia/lightning)

A dynamic and fast CMS built on Drupal. Lightning provides page building tools, editorial workflows, media management and site preview tools.

This installation profile is a base example that ships with an optional demo and developer tools.

### Installation

In addition to standard installation via the UI, Lightning can be built using Drush make.

  ``drush make build-lightning.make ~/Destination/docroot``

Use the ``site-install`` command to install Drupal with the Lightning installation profile.

  ``drush si lightning``

Enable Lightning demo content using the ``lightning-enable`` command.

  ``drush le lightning_demo``

You may now login to your site.

  ``drush uli -l http://mysite.dd``

You may also reset the content of a Lightning Feature if it is enabled.

  ``drush lr lightning_demo``

### Behat tests

Install the drupal-extension for mink/behat from the Lightning profile.

  ``cd profiles/lightning/tests && composer install``

Set up a behat.yml file replacing ``@BASE_URL@`` with the URL to your site and ``@DRUPAL_ROOT@`` with the path to your site on disk.

  ``cp behat.template.yml behat.yml``

Check that behat is installed and running.

  ``bin/behat --help``

Run tests.

  ``bin/behat``

### Documentation

Community documentation for Lightning is available at https://www.drupal.org/node/2472867.

### Resources

The Lightning project is available at: http://drupal.org/project/lightning

The features included with Lightning are available for use in your projects or distributions.

Visit: http://github.com/acquia/lightning-features
