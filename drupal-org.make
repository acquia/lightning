api = 2
core = 7.x

; Lightning Features
projects[lightning_features][version] = "1.x-dev"
projects[lightning_features][type] = "module"
projects[lightning_features][subdir] = "contrib"
projects[lightning_features][download][type] = "git"
projects[lightning_features][download][branch] = "7.x-1.x"

; Lightning Manifests
projects[assemble][version] = "1.x-dev"
projects[assemble][type] = "module"
projects[assemble][subdir] = "contrib"
projects[assemble][download][type] = "git"
projects[assemble][download][branch] = "7.x-1.x"

projects[curate][version] = "1.x-dev"
projects[curate][type] = "module"
projects[curate][subdir] = "contrib"
projects[curate][download][type] = "git"
projects[curate][download][branch] = "7.x-1.x"

; Import
projects[import][version] = "1.x-dev"
projects[import][type] = "module"
projects[import][subdir] = "contrib"
projects[import][download][type] = "git"
projects[import][download][branch] = "7.x-1.x"

projects[migrate][version] = "2.5"
projects[migrate][type] = "module"
projects[migrate][subdir] = "contrib"

projects[migrate_extras][version] = "2.5"
projects[migrate_extras][type] = "module"
projects[migrate_extras][subdir] = "contrib"

; Themes
projects[ember][version] = "2.x-dev"
projects[ember][type] = "theme"
projects[ember][subdir] = "contrib"
projects[ember][download][type] = "git"
projects[ember][download][branch] = "7.x-2.x"

projects[zurb-foundation][version] = "5.x-dev"
projects[zurb-foundation][type] = "theme"
projects[zurb-foundation][subdir] = "contrib"
projects[zurb-foundation][download][type] = "git"
projects[zurb-foundation][download][revision] = "511c618"
projects[zurb-foundation][download][branch] = "7.x-5.x"
; Edit module renamed to Quickedit, Zurb prevents inline editing
; http://drupal.org/node/2332927
projects[zurb-foundation][patch][2332927] = "http://drupal.org/files/issues/zurb-foundation-quickedit-2332927-6.patch"
; zurb_foundation_process_html_tag is destructive
; http://drupal.org/node/2326309
projects[zurb-foundation][patch][2326309] = "http://drupal.org/files/issues/zurb-foundation-strip-cdata-2326309-2.patch"
