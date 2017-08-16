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
the "Manual update steps" section of this file.

## Updating Lightning

### Composer
If you've installed Lightning using our [Composer-based project template](https://github.com/acquia/lightning-project), all you need to do is:

* ```cd /path/to/YOUR_PROJECT```
* ```composer update```
* Run ```drush updatedb && drush cache-rebuild```, or visit ```update.php```,
  to perform automatic database updates. You can also use Drupal Console's
  ```update:execute``` command.
* Perform any necessary manual updates (see below).

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
5. Perform any necessary manual updates (see below).

## Manual update steps

These instructions describe how to update your site's configuration to bring
it in line with a newer version of Lightning. These changes are never made
automatically by Lightning because they have the potential to change the way
your site works.

Follow the instructions starting from the version of Lightning you currently
use. For example, if you are currently running Beta 1 and are trying to update
to Beta 3, you will need to follow the instructions for updating from Beta 1 to
Beta 2, then from Beta 2 to Beta 3, in that order.

## 2.1.6. to 2.1.7
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

## 2.1.5 to 2.1.6
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

## 2.1.4 to 2.1.5
There are no manual update steps for this version.

## 2.1.3 to 2.1.4
There are no manual update steps for this version.

**Note:**  
There is a known issue with Metatag version 8.x-1.1 where you might need to
clear your site's cache after updating. See [Metatag 8.x-1.1 Release notes][metatag8.x-1.1]
and this [related issue][2882954].

As per our Dependency Constraint Policy, Lightning doesn't pin to a specific
version of Metatag, so depending on your your setup, Metatag is likely to be
updated when you update to Lightning 2.1.4. For Composer users, we recommend
pinning to Metatag version 1.0.0. Alternatively, you can be prepared to clear
your site's cache immediately after running `update.php`.

[metatag8.x-1.1]: https://www.drupal.org/project/metatag/releases/8.x-1.1 "Metatag 8.x-1.1 Release notes"
[2882954]: https://www.drupal.org/node/2882954 "Error when updating to 8.x-1.1"

## 2.1.2 to 2.1.3
There are no manual update steps for this version.

## 2.1.1 to 2.1.2
There are no manual update steps for this version.

## 2.1.0 to 2.1.1
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

## 2.0.6 to 2.1.0
There are no manual update steps for this version.

## 2.0.5 to 2.0.6
There are no manual update steps for this version.

## 2.0.4 to 2.0.5
There are no manual update steps for this version.

If you previously used the lightning.extend.yml file to customize your
installation and you have a need to continuously install your application (for
example, in an Acquia Cloud Site Factory instance) you will need to convert your
extend file into a sub-profile of Lightning. See the
[Lightning as a base profile][sub-profile documentation] documentation for more
information.

## 2.0.3 to 2.0.4
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

## 2.0.2 to 2.0.3
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

## 2.0.1 to 2.0.2
* Install the Diff module.
* If you would like to use Lightning's simple contact form, install the
  Contact Form feature from the Lightning package. Alternatively, if you'd like
  to use Contact and Contact Storage's simple form building functionality but
  not Lightning's default configuration, simply install the Contact and
  Contact Storage modules.

## 2.0.0 to 2.0.1
There are no manual update steps for this version.

## 1.14 to 2.0.0
Once you have followed the instructions contained in 1.14 to update to 2.0.0,
there are no further manual update steps.

## 1.13 to 1.14
There are no manual update steps for this version. However, Lightning 1.14
contains a script which will modify your root project's composer.json file in
order to switch your project to the official Drupal.org Packagist and up date
you to Lightning 2.0.0.

If you use the tarball to manage your codebase, you can update directly to the
2.x branch with no manual update steps.

## 1.12 to 1.13
There are no manual update steps for this version.

## 1.11 to 1.12
There are no manual update steps for this version.

## 1.10 to 1.11
There are no manual update steps for this version.

## 1.06 to 1.10
There are no manual update steps for this version.

If you would like to test the new Lightning Preview module and Workspace Preview
System in a development environment, enable the Lightning Preview module from
module listing page. Note that Lightning Preview and WPS are not yet ready for
production environments.

## 1.05 to 1.06
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

### RC7 to 1.00
There are no manual update steps for this version.

### RC6 to RC7
After running `drush updatedb` or visiting `/update.php`:

* Go to *Structure > Content types* and choose **Manage Display** for the
  Landing Page content type.
* Under "Custom Display Settings", make sure the the **Full content** box is
  checked.
* Scroll down and ensure the **Panelize this view mode** and **Allow custom
  overrides of each entity** boxes are checked.
* Press Save.
* Click the **Full content** tab.
* Scroll down and ensure that the **Panelize this view mode**, **Allow custom
  overrides of each entity**, and **Allow panelizer default choice** boxes are
  checked.
* Press Save.
* Click the **Manage form display** tab.
* Move the **Panelizer** field out the Disabled area. Be sure that "Panelizer"
  is selected as the widget type, and press Save.

Depending on what customizations you made to Landing Page prior to the update,
some, all, or none of these setting might already be enabled.

### RC5 to RC6
There are no manual update steps for this version. There are several database
updates. So, as always, be sure to run `drush updatedb` or visit `/update.php`.

### RC4 to RC5
There are no manual update steps for this version.

### RC3 to RC4
* Add ```<br>``` to the Rich Text filter format's list of allowed HTML tags.
* Add the following permissions to Media Creator role:
  * Access the Media overview page
* Add the following permissions to the Media Manager role:
  * Access the Media overview page
  * Administer media bundles
* Edit the **Media** view. Change the **Media** display, and under
  **Page Settings**, set the access permission to "Access the Media overview page".

### RC2 to RC3
There are no manual update steps for this version.

### RC1 to RC2
There are no manual update steps for this version.

### Beta 5 to RC1
There are no manual update steps for this version.

### Beta 4 to Beta 5
* Scheduled updates which change several pieces of content at once were broken.
  The fix is a change to configuration that is owned by the site, so Lightning
  does not attempt to make the change automatically. To implement the fix
  manually:
  * Go to *Configuration > Scheduled Updates Overview > Scheduled Update Types*
  * Edit the **Publish multiple nodes at a certain time** update type
  * Under "Update Runner Settings", select **Default** from the "Update Runner"
    field
  * Select every content type listed in the "Content type" field
  * Press Save

### Beta 3 to Beta 4
There are no manual update steps for this version.

### Beta 2 to Beta 3
* Scheduled updates to content are broken by Lightning's content moderation
  functionality. Beta 3 includes a workaround out-of-the-box which is NOT
  applied by the update. To implement the fix manually:
  * Go to *Configuration > Scheduled Updates Overview > Scheduled Update Types*
  * Edit the **Publish single node at certain time** update type
  * Under "Update Runner Settings", select **Latest Revision** from the
    "Update Runner" field
  * Under "Advanced Runner Options", select **The owner of the update.** from the
    "Run update as" field
  * Press Save

### Beta 1 to Beta 2
* Enable the ```view media``` permission for the ```anonymous``` and
  ```authenticated``` user roles.
* Install the Lightning Workflow module.

[sub-profile documentation]: https://github.com/acquia/lightning/wiki/Lightning-as-a-Base-Profile "Lightning sub-profile documentation"
