This file contains instructions for updating your Lightning-based Drupal site.

Lightning has a two-pronged update process. Out of the box, it provides a great
deal of default configuration for your site, but once it's installed, all that
configuration is "owned" by your site and Lightning cannot safely modify it
without potentially changing your site's behavior or, in a worst-case scenario,
causing data loss.

As it evolves, Lightning's default configuration may change. In certain limited
cases, Lightning will attempt to safely update configuration that it depends on
(which will usually be locked anyway to prevent you from modifying it).
Otherwise, Lightning will leave your configuration alone, respecting the fact
that your site owns it. So, to bring your site fully up-to-date with the latest
default configuration, you must follow the appropriate set(s) of instructions in
the "Configuration updates" section of this file.

## Updating Lightning

### Summary
For a typical site that has a properly configured directory for exporting config
that is managed by your VCS, you would generally follow these steps:

#### In your development or local environment.
1. Read the [release notes](https://github.com/acquia/lightning/releases)
   for the release to which you are updating, and any other releases between
   your current version.
   
1. Update your codebase, replacing `[LIGHTNING_VERSION]` with the most recent
   version of Lightning. For example, `3.1.1`.
  
  ```
  composer self-update
  composer require acquia/lightning:~[LIGHTNING_VERSION] --no-update
  composer update acquia/lightning --with-all-dependencies
  ```
1. Run any database updates.
  
  ```
  drush cache:rebuild
  drush updatedb
  ```
1. Run any Lightning configuration updates.
  
  ```
  drush cache:rebuild
  drush update:lightning
  ```
1. Export the new configuration.

  
  ```
  drush config:export
  ```
1. Commit the code and configuration changes to your VCS and push them to your
   destination environment.
  
#### On your destination environment.

1. Run any database updates.
  
  ```
  drush cache:rebuild
  drush updatedb
  ```

1. Import any configuration changes.
  
  ```
  drush cache:rebuild
  drush config:import
  ```

#### Configuration Management
If you are using configuration management to move your configuration between
development, staging, and production environments, you should export 
configuration after #5 and deploy.

### Composer
If you've installed Lightning using our [Composer-based project template](https://github.com/acquia/lightning-project), all you need to do is:

* ```cd /path/to/YOUR_PROJECT```
* ```composer update```
* Run ```drush updatedb && drush cache:rebuild```, or visit ```update.php```,
  to perform automatic database updates.
* Perform any necessary configuration updates (see below).

### Tarball
**Do not use ```drush pm-update``` or ```drush up``` to update Lightning!**
Lightning includes specific, vetted, pre-tested versions of modules, and
occasionally patches for those modules (and Drupal core). Drush's updater
totally disregards all of that and may therefore break your site.

To update Lightning safely:

1. Download the latest version of Lightning from
   https://www.drupal.org/project/lightning and extract it.
2. Replace your ```profiles/lightning``` directory with the one included in the
   fresh copy of Lightning.
3. Replace your ```core``` directory with the one included in the fresh copy
   Lightning.
4. Visit ```update.php``` or run ```drush updatedb``` to perform any necessary
   database updates.
5. Perform any necessary configuration updates and/or migrations (see below).

## Update instructions

These instructions describe how to update your site to bring it in line with a
newer version of Lightning. Lightning does not make these changes automatically
because they may change the way your site works.

However, as of version 3.0.2, Lightning provides a Drush 9 command which *can*
perform updates automatically, confirming each change interactively as it goes.
If you intend to perform all the updates documented here, this can save quite
a bit of time!

That said, though, some of these updates involve complicated data migrations.
Due to their complexity, Lightning *never* automates them -- you will need to
take some manual action to complete these updates, which are denoted as such
below.

### Automatic configuration updates

Ensure Drush 9 is installed, then switch into the web root of your
Lightning installation and run:

```
$ drush update:lightning
```


To run all available configuration updates without any prompting, use:

```
$ drush update:lightning --no-interaction
```

If you'd rather do the updates manually, follow the instructions below,
starting from the version of Lightning you currently use. For example, if you
are currently running 2.2.0 and are trying to update to 2.2.6, you will need to
follow the instructions for updating from 2.2.0 to 2.2.1, then from 2.2.1 to
2.2.2, in that order.

### 4.1.1 to 4.1.2
There are no manual update steps for this version.

### 4.1.0 to 4.1.1
There are no manual update steps for this version.

### 4.1.0-beta1 to 4.1.0
**IMPORTANT!!** This release includes Pathauto 1.6. If you are already using
Pathauto 1.5 or lower, you MUST update Pathauto before updating to this release
of Lightning! See https://www.drupal.org/project/drupal/releases/8.8.0, under
the section "Changes to path aliases and a critical note for sites using the
Pathauto contributed module":

> If you have the contributed Pathauto module enabled, you must update to the
> latest version of Pathauto _before_ you update to Drupal 8.8.0. **Failure to
> update Pathauto before updating core could result in data loss.** Drupal
> 8.8.0 declares a conflict with Pathauto 8.x-1.5 or lower for this reason.

### 4.0.5 to 4.1.0-beta1
There are no manual update steps for this version.

### 4.0.4 to 4.0.5
* If you would like to enable support for autosave on your site, install the
  Autosave Form and Conflict modules.
* If you would like to enable improved redirect handling, install the Redirect
  module.

### 4.0.3 to 4.0.4
There are no manual update steps for this version.

### 4.0.2 to 4.0.3
There are no manual update steps for this version.

### 4.0.1 to 4.0.2
There are no manual update steps for this version.

### 4.0.0 to 4.0.1
There are no manual update steps for this version.

### 3.3.1 to 4.0.0
Lightning 4.0.0 includes Lightning Layout 2.0, which uses Layout Builder in
place of Panels and Panelizer. However, both modules are still packaged with
Lightning Layout, allowing you to migrate your layouts manually to Layout
Builder as needed. An automated migration path will eventually be introduced,
but until then, any Panelizer/Panels layouts you have should continue to work
as before.

### 3.3.0 to 3.3.1
There are no manual update steps for this version.

### 3.2.9 to 3.3.0
There are no manual update steps for this version. However, there is a known
bug in the update path of the Consumers module (packaged with Lightning API)
which may cause problems when updating from Drupal 8.6 to 8.7 *and* updating
from an older version of Consumers at the same time. A temporary workaround is
to update the Consumers module to its latest version *before* updating to Drupal
core 8.7. If you have a Composer-based build of Lightning, you can update
Consumers like so:

```
$ composer update drupal/consumers --with-all-dependencies
$ drush updatedb
```

Once this is done, you should be able to update to Drupal 8.7 without trouble.
See https://www.drupal.org/project/consumers/issues/3052959 for more information
about this bug.

### 3.2.8 to 3.2.9
There are no manual update steps for this version.

### 3.2.7 to 3.2.8
There are no manual update steps for this version.

### 3.2.6 to 3.2.7
There are no manual update steps for this version.

### 3.2.5 to 3.2.6
* If you would like to enable support for user account pictures (avatars):
  1. Create a new image field on user accounts. In Lightning Core, this field
     is called "Picture" by default and its machine name is user_picture.
  2. Set the file default-avatar.png, contained in the `images` directory
     of Lightning Core, to be the default image for the field.
  2. Customize the display of the "Compact" view mode for user accounts, and
     ensure that the new image field is displayed using the Thumbnail image
     style.
* If you would like to display the media browser in a modal window, rather than
  in an iFrame, follow these instructions:
  1. Create a clone of the media browser which will only be used when embedding
     media using the WYSIWYG editor. There is no easy way to duplicate the media
     browser from the administrative backend, but you can run a bit of PHP code
     at the command line with Drush (or, if you have Devel installed, at the
     `/devel/php` path) to do it:
```
drush php:eval "entity_load('entity_browser', 'media_browser')
  ->createDuplicate()
  ->setName('ckeditor_media_browser')
  ->setLabel('Media browser (CKEditor)')
  ->save();"
```
  2. Configure the "Media browser" embed button to use the duplicate you just
     created.
  3. Grant permissions to use the duplicate entity browser to the "Media
     creator" and "Media manager" user roles.
  4. If you have Lightning Roles installed, you'll also need to grant access to
     the duplicate by executing this PHP code (again, this can be done at the
     command line with Drush, as in this example, or at `/devel/php` if you have
     Devel installed):
```
drush php:eval "Drupal::service('lightning.content_roles')
  ->grantPermissions('creator', [
    'use ckeditor_media_browser entity browser pages',
  ]);"
```
  5. Edit the pre-existing media browser -- _not_ the duplicate -- to use the
     Modal display plugin. Leave the "Width" and "Height" options empty to make
     the modal dialog responsive, set the link text to "Add media", and disable
     auto-open. Save the changes to the media browser.

### 3.2.4 to 3.2.5
There are no manual update steps for this version.

### 3.2.3 to 3.2.4
* Configure the "Show in media library" field of the "Audio file" media type to
  be non-translatable.
* Configure the "Show in media library" field of the "Video" media type to be
  non-translatable.
* Configure the "Show in media library" field of the "Video file" media type to
  be non-translatable.
* If you have the "Moderation history" view installed:
  1. Replace the author field, which displays the original creator of the node,
     with a new field that displays the author of the revision. This will
     require you to add a relationship to the revision author.
  2. Replace the creation time field, which displays the time that the node was
     originally created, with a new field that displays the time that the
     revision was created.
  3. Rewrite the content of the "Moderation state" field to this Twig template
     code:
```
Set to <strong>{{ moderation_state }}</strong> on
{{ revision_timestamp }} by {{ revision_uid }}
```

### 3.2.2 to 3.2.3
There are no manual update steps for this version.

### 3.2.1 to 3.2.2
There are no manual update steps for this version.

### 3.2.0 to 3.2.1
* Install the Media Library module (in the "Core (Experimental)" group). Then,
  for each media type, create a new display, called "Media library", containing
  only the thumbnail image, displayed using the thumbnail image style.
* Install the new "Media Slideshow" module (in the "Lightning" group).
* Install the Moderation Dashboard module. By default, this will cause users to
  be redirected to their moderation dashboard upon logging in. To disable this
  behavior, run the following Drush command:
```
drush config:set moderation_dashboard.settings redirect_on_login 0
```

### 3.1.7 to 3.2.0
* If you have any sub-profiles (regardless of whether or not they extend
  Lightning), you must change their info files to work with Drupal 8.6.0:
  * Change `base profile` to a string, containing the name of the base
    profile. For example: `base profile: lightning`.
  * Change the `dependencies` key to `install`.
  * If you have any excluded dependencies or themes, merge them into a
    single array, with the key `exclude`.
  For example, an 8.6.0-compatible sub-profile info file will look something
  like this:
```
name: My Profile
core: 8.x
type: profile
base profile: lightning
install:
  - paragraphs
  - slick_entityreference
exclude:
  - lightning_search
  - pathauto
  - bartik
```

### 3.1.6 to 3.1.7
* There are no manual update steps for this version.

### 3.1.5 to 3.1.6
* If you would like to create media items for audio files, enable the new
  Media Audio module (lightning_media_audio).
* Rename every instance of the "Save to media library" field (present on all
  media types by default) to "Show in media library".
* If you would like to create media items for video files, create a new
  media type called "Video file", using the "Video file" source. Then, create
  two new view displays for this media type: one called "Thumbnail", which
  only displays the media thumbnail using the "Medium" image style, and one
  called "Embedded", which displays the "Video file" field using the "Video"
  formatter. Additionally, create a form display for this media type, using
  the "Media browser" form mode, which displays, in order:
  1. The "Name" field using the "Text field" widget
  2. The "Video file" field using the "File" widget
  3. The "Show in media library" field using the "Single on/off checkbox" widget
  4. The "Published" field using the "Single on/off checkbox" widget
* If you would like to be able to change the moderation states of content
  without having to visit the edit form, install the Moderation Sidebar module.
* If you'd like to streamline the Editorial workflow, edit it and make the
  following modifications:
  1. Rename the "Review" transition to "Send to review".
  2. Rename the "Restore" transition to "Restore from archive".
  3. Remove the "Restore to draft" transition, and edit the "Create new draft"
     transition to allow content to be transitioned from the Archived state to
     the Draft state.

### 3.1.4 to 3.1.5
There are no manual update steps for this version.

Note that this release includes an update to Drupal core which security updates
some of its dependencies. As such, you might need to include `drupal/core` in
the list of arguments you pass to `composer update` if any of its dependencies
are locked at older versions in your project. For example:

```
$ composer update acquia/lightning drupal/core --with-all-dependencies
```

### 3.1.3 to 3.1.4
* **NOTE: This is a _fully manual update_ that involves a data migration!**
  Lightning Scheduler has been completely rewritten, and now stores scheduled
  moderation state transitions in a pair of new base fields. You will need to
  migrate any existing scheduled transitions from the old base fields to the
  new ones. After completing database updates, Lightning Scheduler will link
  you to a UI where you can run the migration. Alternatively, you can do it
  at the command line (Drush 9 only) by running
  `drush lightning:scheduler:migrate`.

  If you have scheduled transitions attached to a specific entity type and
  you'd like to discard those transitions without migrating them (test data,
  for example), you can "purge" it in the UI, or at the command line by running
  `drush lightning:scheduler:purge ENTITY_TYPE_ID`. Purging must be done one
  entity type at a time, e.g. `drush lightning:scheduler:purge paragraph`.
  
  Once all entity types have been migrated or purged, the old base fields will
  need to be uninstalled. You can perform this clean-up work automatically by
  running `drush entity-updates`.

**Note:** The Lightning Scheduler migration in Lightning 3.1.4 affects actual
  content entities. As such, it will need to be run on your production
  database.

### 3.1.2 to 3.1.3
There are no manual update steps for this version.

### 3.1.1 to 3.1.2
There are no manual update steps for this version.

### 3.1.0 to 3.1.1
* If you have Lightning Roles and Lightning Workflow installed, grant the
  "View any unpublished content" and "View latest version" roles for each
  provided "reviewer" role.  

### 3.0.3 to 3.1.0
* Edit the **Media** view, and if it has an exposed filter called "Media Type",
  modify the filter label to "Type" and change its URL identifier to "type".

### 3.0.2 to 3.0.3
There are no manual update steps for this version.

### 3.0.1 to 3.0.2
There are no manual update steps for this version.

### 3.0.0 to 3.0.1
There are no manual update steps for this version. 

### 2.2.8 to 3.0.0
There are no manual update steps for this version.

**Note:** The following modules are no longer provided or used by Lightning. If
you use these modules you will need to add them to your project's composer.json
file or include them in your codebase using another method:

* Scheduled Updates (`scheduled_updates`)
* Features (`features`)
* Configuration Update Manager (`config_update`)
* Entity (`entity`)
* Media Entity (`media_entity`)
* Media Entity Document (`media_entity_document`)
* Media Entity Image (`media_entity_image`)

For example, if you use Features to manage your configuration, you can include
it in your project with the following command:

```
composer require drupal/features
```

**Note:** See "3.x branch" section above for detailed instructions.

**Note:** You will likely need to update Lightning's constraint to get the 3.x
branch. The following is a good starting point, but additional commands might be
needed depending on your specific requirements and constraints:

```
composer require acquia/lightning:~3.0.0 --no-update
composer update acquia/lightning --with-all-dependencies
```   

### 2.2.7 to 2.2.8
There are no manual update steps for this version.

### 2.2.6 to 2.2.7
There are no manual update steps for this version.

### 2.2.5 to 2.2.6
There are no manual update steps for this version.

### 2.2.4 to 2.2.5
There are no manual update steps for this version.

### 2.2.3 to 2.2.4
* Visit *Structure > Media types*. For each media type, click "Manage display"
  and select the "Embedded" display. Then drag the "Name" field into the
  "Disabled" section and press "Save".
* If you previously used a sub-profile to exclude Lightning Workflow's
  "Schedule Publication" sub-component (its machine name is
  `lightning_scheduled_updates`), you will need to update your sub-profile's
  excluded dependencies to exclude `lightning_scheduler` instead, which
  replaces `lightning_scheduled_updates` in this release.
* Uninstall Scheduled Updates and Lightning Scheduled Updates, and enable the
  Lightning Scheduler module.
  
  ```
  drush pm-uninstall scheduled_updates lightning_scheduled_updates
  drush pm-enable lightning_scheduler
  ```
* To migrate to Content Moderation, install the wbm2cm module and Drush (8.x or
  9.x). **Back up your database**, then run ```drush wbm2cm-migrate``` to run
  the migration.

**Note:** The Workbench Moderation to Content Moderation migration in Lightning
2.2.4 affects actual content entities. As such, it will need to be run on your
production database. If you have previously run the migration locally and
deployed your config, you will run into issues with your config trying to
disable Workbench Moderation - since the migration hasn't taken place on that
database yet.

If you store and deploy your config via a VCS, it is recommended that you skip
the last manual step during your initial update to 2.2.4. Once the other config
changes have been deployed, test the migration locally on an export of your
production database before ultimately running it in your production environment.
As with any script that affects content, be sure to take a backup of your
production database before running the script.  

### 2.2.2 to 2.2.3
There are no manual update steps for this version.

### 2.2.1 to 2.2.2
There are no manual update steps for this version.

### 2.2.0 to 2.2.1
* Visit *Structure > Content types*. For each moderated content type, click
  "Manage form display", then drag the "Publishing status" field into the
  "Disabled" section and press "Save".

### 2.1.8 to 2.2.0
There are no manual update steps for this version. 

### 2.1.7 to 2.1.8
* Lightning now ships with support for image cropping, using the Image Widget
  Crop module. To use it for the Image media bundle (the default behavior in
  new Lightning sites), do the following:
  * Install the Image Widget Crop module.
  * Visit *Structure > Media bundles*. For the Image media bundle, choose
    "Manage form display".
  * If the Image field is enabled, set its widget type to "ImageWidget crop",
    and configure it like so:
    * Only "Freeform" should be selected for "Crop Type".
    * "Always expand crop area" should be checked.
    * "Show links to uploaded files" should be checked.
    * "Show Remove button" should be checked.
  * Press "Update", then "Save".
  * Go to the "Media browser" tab. If the Image field is enabled, set its
    widget type to "ImageWidget crop" and configure it like so:
    * Only "Freeform" should be selected for "Crop Type".
    * "Always expand crop area" should be checked.
    * "Show links to uploaded files" should NOT be checked.
    * "Show Remove button" should NOT be checked.
  * Press "Update", then "Save".
  * By default, Image Widget Crop uses a CDN-hosted copy of the Cropper
    JavaScript library. Lightning includes a copy of Cropper as well, which
    you can use instead of the CDN-hosted version if you prefer to. To use
    Lightning's included copy of the library, visit *Configuration > Media >
    ImageWidgetCrop settings* and make the following changes under "Cropper
    library settings":
    * Set "Custom Cropper library" to
    ```libraries/cropper/dist/cropper.min.js```.
    * Set "Custom Cropper CSS file" to
   ```libraries/cropper/dist/cropper.min.css```.
* Lightning now has support for bulk uploading media assets. To enable this
  feature, install the Bulk Media Upload module from the Lightning Package.
* New installs of Lightning that include the Workflow component will now place
  the Operations drop-button as the last column of the /admin/content view.
  To make this change in your existing Lightning installation, edit the
  **content** view, move the Operations field to the end of the list of fields,
  and save the changes.

### 2.1.6. to 2.1.7
* **IMPORTANT!** Page Manager is no longer a dependency of Lightning Layout,
  and it will no longer ship with Lightning as of the next release. Therefore,
  if you are actively using Page Manager, you must add it to your project as an
  explicit dependency in order to continue to using it. Otherwise, **you must
  uninstall it before updating to the next version of Lightning, or your site
  may break.**
* **IMPORTANT!** Lightning has added support for pulling front-end JavaScript
  libraries into your project using Composer, via [Asset Packagist](https://asset-packagist.org).
  This requires a few simple, one-time changes to your project's root
  composer.json. Note that, **without these changes, some functionality in
  future Lightning releases will not work.** The required changes, and
  instructions on how to make them (either manually, or automatically using a
  Lightning-provided script) are [documented here](http://lightning.acquia.com/blog/round-your-front-end-javascript-libraries-composer).
* Lightning now supports exposing all Drupal entities as JSON, in the standard
  JSON API format. To enable this feature, install the Content API module from
  the Lightning package.
* If Lightning's content role functionality is available, grant all "creator"
  content roles the following permissions, as desired:
  * **Toolbar**: Use the administration toolbar
  * **Quick Edit**: Access in-place editing
  * **Contextual Links**: Use contextual links

### 2.1.5 to 2.1.6
This version of Lightning adds the ability to choose an image style, alt text,
and other settings each time you embed an image in a WYSIWYG editor, rather
that needing to rely on view modes. To enable this functionality:

1. As always, visit ```update.php``` or run ```drush updatedb``` to perform
   database updates.
1. Clear all caches.
1. Under *Configuration > Content Authoring > Text formats and editors*,
   configure the **Rich Text** filter format. Under "Filter settings", open the
   tab labeled "Limit allowed HTML tags and correct faulty HTML".
1. In the "Allowed HTML tags" text field, you should see a tag like
   `<drupal-entity data-*>`. Change it to `<drupal-entity data-* alt title>`.
1. Save the filter format.
1. Under *Configuration > Content Authoring > Text editor embed buttons*,  edit
   the "Media browser" embed button.
1. Under "Allowed Entity Embed Display plugins", ensure that the "Media Image"
   checkbox is checked.
1. Save the embed button.
1. If you would like to allow authors to choose how embedded media should be
   displayed, go to *Configuration > System > Lightning > Media*, ensure that
   the box labeled "Allow users to choose how to display embedded media" is
   checked, then submit the form. If the box is not checked, Lightning will
   automatically choose a preferred display method (the recommended, default
   behavior).

### 2.1.4 to 2.1.5
There are no manual update steps for this version.

### 2.1.3 to 2.1.4
There are no manual update steps for this version.

**Note:**  
There is a known issue with Metatag version 8.x-1.1 where you might need to
clear your site's cache after updating. See
[Metatag 8.x-1.1 Release notes][metatag8.x-1.1] and this
[related issue][2882954].

As per our Dependency Constraint Policy, Lightning doesn't pin to a specific
version of Metatag, so depending on your your setup, Metatag is likely to be
updated when you update to Lightning 2.1.4. For Composer users, we recommend
pinning to Metatag version 1.0.0. Alternatively, you can be prepared to clear
your site's cache immediately after running `update.php`.

[metatag8.x-1.1]: https://www.drupal.org/project/metatag/releases/8.x-1.1 "Metatag 8.x-1.1 Release notes"
[2882954]: https://www.drupal.org/node/2882954 "Error when updating to 8.x-1.1"

### 2.1.2 to 2.1.3
There are no manual update steps for this version.

### 2.1.1 to 2.1.2
There are no manual update steps for this version.

### 2.1.0 to 2.1.1
* To allow fields that use the media browser to filter to only the media types
  accepted by the field, do the following:
    * Edit the **Browser** display of the **Media** view.
    * Add the **Bundle** contextual filter, to the current display only, with
      the following settings:
      * When the filter value is NOT available, provide a default value:
        * Type: Entity Browser Context
        * Context key: ```target_bundles```
        * Fallback value: ```all```
        * Multiple values: OR
      * When the filter value IS available or a default is provided:
        * Specify validation criteria: Yes
        * Validator: Media bundle
        * Multiple arguments: One or more IDs separated by , or +
        * Action to take if filter value does not validate: Display all results
          for the specified field
      * Under the "More" section, "Allow multiple values" should be checked.
    * If the view has the media bundle as an exposed filter (most likely named
      "Media: Bundle"), edit it and set the "Yield to argument" select box to
      the name of the argument you just created (which will probably be "Media:
      Bundle"). If you don't see the "Yield to argument" select box, clear all
      caches and try again.
    * Save the view.

### 2.0.6 to 2.1.0
There are no manual update steps for this version.

### 2.0.5 to 2.0.6
There are no manual update steps for this version.

### 2.0.4 to 2.0.5
There are no manual update steps for this version.

If you previously used the lightning.extend.yml file to customize your
installation and you have a need to continuously install your application (for
example, in an Acquia Cloud Site Factory instance) you will need to convert your
extend file into a sub-profile of Lightning. See the
[Lightning as a base profile][sub-profile documentation] documentation for more
information.

### 2.0.3 to 2.0.4
* Edit the **Scheduled update** field on any content type that has it. Click
  **Field settings*, set "Allowed number of values" to "Unlimited" and save.
  Then click **Edit**, rename the field to "Scheduled updates", and save.
* If you have the Image Browser entity browser available:
  * Go to *Configuration > Content authoring > Entity browsers* and edit the
    **Image Browser** entity browser.
  * Click **Next**.
  * Empty the "Width of the modal" and "Height of the modal" text fields.
  * Click **Next**, then proceed through the rest of the wizard without changing
    anything else. Then click **Finish** to save the entity browser.
* If you have Lightning Workflow installed, add the *View moderation states*
  permission to all content reviewer roles.
* If you have Lightning Workflow installed, Go to *Structure > Views* and edit
  the **Content** view.
  * Expand the **Advanced** section and under **Relationships** click on 'latest
    revision'.
  * Un-check the "Require this relationship" checkbox and click **Apply (all
    displays)**.
  * Save the view.

### 2.0.2 to 2.0.3
* If you have the Landing Page content type installed, there are several manual
  update steps (to be performed in order):
  * Create a formatted text field on the Landing Page content type. You can
    re-use the standard node Body field for this purpose.
  * Customize the Teaser view mode of the Landing Page content type. Do not
    enable Panelizer for it. Add the new text field to the display.
  * Disable Panelizer for the Default view mode of the Landing Page content
    type, then add the new text field to the display.
* If you would like search functionality:
  * If you would like to use the Drupal database as a search backend, enable
    the Search API DB module. You don't need to enable this module if you'd
    rather use Apache Solr (or something else) as a backend.
  * Install the Lightning Search component. If you have already enabled Search
    API DB, Lightning Search will create a search server on top of the database
    backend, and use it to power the Content search index. Otherwise, you will
    need to create the server manually, then point the index to it.
* Grant the "Access the Content overview page" and (if available) "Use the
  Archive transition" permissions to all content type reviewer roles, if there
  are any.
* Edit the Content view, if you have it, and add the following:
  * A required relationship to **Content latest revision**.
  * The **Forward revision(s) exist** filter. For parity with a clean Lightning
    installation, label it "Has unpublished edit(s)".

### 2.0.1 to 2.0.2
* Install the Diff module.
* If you would like to use Lightning's simple contact form, install the
  Contact Form feature from the Lightning package. Alternatively, if you'd like
  to use Contact and Contact Storage's simple form building functionality but
  not Lightning's default configuration, simply install the Contact and
  Contact Storage modules.

### 2.0.0 to 2.0.1
There are no manual update steps for this version.

### 1.14 to 2.0.0
Once you have followed the instructions contained in 1.14 to update to 2.0.0,
there are no further manual update steps.

### 1.13 to 1.14
There are no manual update steps for this version. However, Lightning 1.14
contains a script which will modify your root project's composer.json file in
order to switch your project to the official Drupal.org Packagist and up date
you to Lightning 2.0.0.

If you use the tarball to manage your codebase, you can update directly to the
2.x branch with no manual update steps.

### 1.12 to 1.13
There are no manual update steps for this version.

### 1.11 to 1.12
There are no manual update steps for this version.

### 1.10 to 1.11
There are no manual update steps for this version.

### 1.06 to 1.10
There are no manual update steps for this version.

If you would like to test the new Lightning Preview module and Workspace Preview
System in a development environment, enable the Lightning Preview module from
module listing page. Note that Lightning Preview and WPS are not yet ready for
production environments.

### 1.05 to 1.06
There are no manual update steps for this version.

### 1.04 to 1.05
There are no manual update steps for this version.

### 1.03 to 1.04
* Go to *Structure > Views* and edit the **Media** view.
* Edit the **Browser** display and configure the pager.
* Change "Items to display" to 12 and press Apply.
* Repeat this configuration for the **Image Browser** display.
* Save the view.

### 1.02 to 1.03
There are no manual update steps for this version.

### 1.01 to 1.02
There are no manual update steps for this version.

### 1.00 to 1.01
There are no manual update steps for this version.

[sub-profile documentation]: https://github.com/acquia/lightning/wiki/Lightning-as-a-Base-Profile "Lightning sub-profile documentation"
