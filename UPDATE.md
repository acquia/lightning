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
* Run ```drush updatedb``` or visit ```update.php``` to perform automatic database updates.
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
