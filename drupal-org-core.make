core = 8.x
api = 2
projects[drupal][type] = core
projects[drupal][download][type] = git
projects[drupal][download][url] = https://git.drupal.org/project/drupal.git
projects[drupal][download][branch] = 8.6.x
projects[drupal][download][tag] = 8.6.0-rc1
projects[drupal][patch][] = https://www.drupal.org/files/issues/2869592-remove-update-warning-7.patch
projects[drupal][patch][] = https://www.drupal.org/files/issues/2885441-2.patch
projects[drupal][patch][] = https://www.drupal.org/files/issues/2018-07-09/2815221-105.patch
projects[drupal][patch][] = https://www.drupal.org/files/issues/2018-07-12/1356276-473.patch
projects[drupal][patch][] = https://www.drupal.org/files/issues/2018-07-09/2914389-8-do-not-test.patch