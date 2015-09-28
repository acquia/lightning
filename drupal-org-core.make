; A separate drupal-org-core.make file for core patches

api = 2
core = 8.x
projects[drupal][type] = core
projects[drupal][version] = 8.0.0-beta15

; CORE PATCHES

; Allow install profiles to change the system requirements
projects[drupal][patch][] = "https://www.drupal.org/files/issues/1772316-34.patch"

