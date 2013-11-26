api = 2
core = 7.x

projects[breakpoints][version] = "1.1"
projects[breakpoints][type] = "module"
projects[breakpoints][subdir] = "contrib"

projects[ctools][version] = "1.x-dev"
projects[ctools][type] = "module"
projects[ctools][subdir] = "contrib"
projects[ctools][download][type] = "git"
projects[ctools][download][revision] = "e81da7a"
projects[ctools][download][branch] = "7.x-1.x"
; Introduce UUIDs onto panes & displays for
; better exportability & features compatibility
; (ctools patch from panels queue)
; http://drupal.org/node/1277908#comment-7216356
projects[ctools][patch][1277908] = "http://drupal.org/files/ctools-uuids_for_exported_objects-1277908-118.patch"
; Update the token replacements in ctools to work against a fully rendered page.
; http://drupal.org/node/955070#comment-7751253
projects[ctools][patch][955070] = "http://drupal.org/files/ctools-fix_token_processing-955070-5.patch"

projects[demonstratie_panels][version] = "1.x-dev"
projects[demonstratie_panels][type] = "module"
projects[demonstratie_panels][subdir] = "contrib"
projects[demonstratie_panels][download][type] = "git"
projects[demonstratie_panels][download][branch] = "7.x-1.x"
projects[demonstratie_panels][download][revision] = "9566cbd"

projects[devel][version] = "1.3"
projects[devel][type] = "module"
projects[devel][subdir] = "contrib"

projects[diff][version] = "3.2"
projects[diff][type] = "module"
projects[diff][subdir] = "contrib"

projects[entity][version] = "1.2"
projects[entity][type] = "module"
projects[entity][subdir] = "contrib"

projects[features][version] = "2.x-dev"
projects[features][type] = "module"
projects[features][subdir] = "contrib"
projects[features][download][type] = "git"
projects[features][download][revision] = "93ff6cd"
projects[features][download][branch] = "7.x-2.x"

projects[jquery_update][version] = "2.3"
projects[jquery_update][type] = "module"
projects[jquery_update][subdir] = "contrib"

projects[libraries][version] = "2.1"
projects[libraries][type] = "module"
projects[libraries][subdir] = "contrib"

projects[link][version] = "1.2"
projects[link][type] = "module"
projects[link][subdir] = "contrib"

projects[module_filter][version] = "2.0-alpha1"
projects[module_filter][type] = "module"
projects[module_filter][subdir] = "contrib"

projects[navbar][version] = "1.x-dev"
projects[navbar][type] = "module"
projects[navbar][subdir] = "contrib"
projects[navbar][download][type] = "git"
projects[navbar][download][revision] = "dd542e1"
projects[navbar][download][branch] = "7.x-1.x"
; Menu icons for contrib modules
; http://drupal.org/node/1954912
projects[navbar][patch][1954912] = "http://drupal.org/files/navbar-contrib-icons-1954912-6.patch"

projects[pathauto][version] = "1.2"
projects[pathauto][type] = "module"
projects[pathauto][subdir] = "contrib"

projects[pm_existing_pages][version] = "1.4"
projects[pm_existing_pages][type] = "module"
projects[pm_existing_pages][subdir] = "contrib"

projects[responsive_preview][version] = "1.x-dev"
projects[responsive_preview][type] = "module"
projects[responsive_preview][subdir] = "contrib"
projects[responsive_preview][download][type] = "git"
projects[responsive_preview][download][revision] = "94cdec7"
projects[responsive_preview][download][branch] = "7.x-1.x"

projects[role_export][version] = "1.0"
projects[role_export][type] = "module"
projects[role_export][subdir] = "contrib"

projects[rules][version] = "2.6"
projects[rules][type] = "module"
projects[rules][subdir] = "contrib"

projects[token][version] = "1.5"
projects[token][type] = "module"
projects[token][subdir] = "contrib"

projects[strongarm][version] = "2.0"
projects[strongarm][type] = "module"
projects[strongarm][subdir] = "contrib"

; Assemble
projects[assemble][version] = "1.0"
projects[assemble][type] = "module"
projects[assemble][subdir] = "contrib"
projects[assemble][download][type] = "git"
projects[assemble][download][branch] = "7.x-1.x"

projects[bean][version] = "1.x-dev"
projects[bean][type] = "module"
projects[bean][subdir] = "contrib"
projects[bean][download][type] = "git"
projects[bean][download][revision] = "3926c82"
projects[bean][download][branch] = "7.x-1.x"

projects[bean_tax][version] = "2.x-dev"
projects[bean_tax][type] = "module"
projects[bean_tax][subdir] = "contrib"
projects[bean_tax][download][type] = "git"
projects[bean_tax][download][revision] = "f796c8e"
projects[bean_tax][download][branch] = "7.x-2.x"

projects[colorbox][version] = "2.4"
projects[colorbox][type] = "module"
projects[colorbox][subdir] = "contrib"

projects[entityreference][version] = "1.x-dev"
projects[entityreference][type] = "module"
projects[entityreference][subdir] = "contrib"
projects[entityreference][download][type] = "git"
projects[entityreference][download][revision] = "1c176da"
projects[entityreference][download][branch] = "7.x-1.x"

projects[entityreference_prepopulate][version] = "1.3"
projects[entityreference_prepopulate][type] = "module"
projects[entityreference_prepopulate][subdir] = "contrib"

projects[fape][version] = "1.x-dev"
projects[fape][type] = "module"
projects[fape][subdir] = "contrib"
projects[fape][download][type] = "git"
projects[fape][download][revision] = "1143ee2"
projects[fape][download][branch] = "7.x-1.x"
projects[fape][patch][1846156] = "http://drupal.org/files/fape-1846156-5.patch"

projects[fences][version] = "1.x-dev"
projects[fences][type] = "module"
projects[fences][subdir] = "contrib"
projects[fences][download][type] = "git"
projects[fences][download][revision] = "67206b5"
projects[fences][download][branch] = "7.x-1.x"

projects[field_group][version] = "1.x-dev"
projects[field_group][type] = "module"
projects[field_group][subdir] = "contrib"
projects[field_group][download][type] = "git"
projects[field_group][download][revision] = "9cdde2b"
projects[field_group][download][branch] = "7.x-1.x"

projects[fieldable_panels_panes][version] = "1.x-dev"
projects[fieldable_panels_panes][type] = "module"
projects[fieldable_panels_panes][subdir] = "contrib"
projects[fieldable_panels_panes][download][type] = "git"
projects[fieldable_panels_panes][download][revision] = "1bda8c9"
projects[fieldable_panels_panes][download][branch] = "7.x-1.x"

projects[gridbuilder][version] = "1.0-alpha2"
projects[gridbuilder][type] = "module"
projects[gridbuilder][subdir] = "contrib"

projects[json2][version] = "1.1"
projects[json2][type] = "module"
projects[json2][subdir] = "contrib"

projects[layout][version] = "1.0-alpha6"
projects[layout][type] = "module"
projects[layout][subdir] = "contrib"

projects[metatag][version] = "1.0-beta5"
projects[metatag][type] = "module"
projects[metatag][subdir] = "contrib"

projects[panelizer][version] = "3.x-dev"
projects[panelizer][subdir] = "contrib"
projects[panelizer][download][type] = "git"
projects[panelizer][download][revision] = "1e050d3"
projects[panelizer][download][branch] = "7.x-3.x"

projects[panels][version] = "3.x-dev"
projects[panels][type] = "module"
projects[panels][subdir] = "contrib"
projects[panels][download][type] = "git"
projects[panels][download][revision] = "2bb470e"
projects[panels][download][branch] = "7.x-3.x"
; Introduce UUIDs onto panes & displays for better 
; exportability & features compatibility
; http://drupal.org/node/1277908#comment-6771122
projects[panels][patch][1179034_1277908] = "http://drupal.org/files/panels-1179034-41_____panels-uuids-127790-100__-80.patch"

projects[panopoly_images][version] = "1.x-dev"
projects[panopoly_images][type] = "module"
projects[panopoly_images][subdir] = "contrib"
projects[panopoly_images][download][type] = "git"
projects[panopoly_images][download][revision] = "b57b48f"
projects[panopoly_images][download][branch] = "7.x-1.x"

projects[panopoly_magic][version] = "1.x-dev"
projects[panopoly_magic][type] = "module"
projects[panopoly_magic][subdir] = "contrib"
projects[panopoly_magic][download][type] = "git"
projects[panopoly_magic][download][revision] = "4db638a"
projects[panopoly_magic][download][branch] = "7.x-1.x"

projects[panopoly_theme][version] = "1.x-dev"
projects[panopoly_theme][type] = "module"
projects[panopoly_theme][subdir] = "contrib"
projects[panopoly_theme][download][type] = "git"
projects[panopoly_theme][download][revision] = "7715ded"
projects[panopoly_theme][download][branch] = "7.x-1.x"
; Remove makefile from Panopoly Theme
; http://drupal.org/node/1904766
projects[panopoly_theme][patch][1904766] = "http://drupal.org/files/panopoly_theme-makefile-free-1904766-5.patch"

projects[panopoly_widgets][version] = "1.x-dev"
projects[panopoly_widgets][type] = "module"
projects[panopoly_widgets][subdir] = "contrib"
projects[panopoly_widgets][download][type] = "git"
projects[panopoly_widgets][download][revision] = "15c8dce"
projects[panopoly_widgets][download][branch] = "7.x-1.x"
; Use Panopoly Widgets in Demo Framework
; http://drupal.org/node/1949710
projects[panopoly_widgets][patch][1949710] = "http://drupal.org/files/panopoly_widgets-demo-framework-1949710-10.patch"

projects[picture][version] = "1.x-dev"
projects[picture][type] = "module"
projects[picture][subdir] = "contrib"
projects[picture][download][type] = "git"
projects[picture][download][revision] = "3d9fe6c"
projects[picture][download][branch] = "7.x-1.x"

projects[respondjs][version] = "1.1"
projects[respondjs][type] = "module"
projects[respondjs][subdir] = "contrib"

projects[taxonomy_entity_index][version] = "1.0-beta6"
projects[taxonomy_entity_index][type] = "module"
projects[taxonomy_entity_index][subdir] = "contrib"

projects[views][version] = "3.7"
projects[views][type] = "module"
projects[views][subdir] = "contrib"

projects[views_autocomplete_filters][version] = "1.0-beta2"
projects[views_autocomplete_filters][type] = "module"
projects[views_autocomplete_filters][subdir] = "contrib"

projects[views_field_view][version] = "1.x-dev"
projects[views_field_view][type] = "module"
projects[views_field_view][subdir] = "contrib"
projects[views_field_view][download][type] = "git"
projects[views_field_view][download][revision] = "db93080"
projects[views_field_view][download][branch] = "7.x-1.x"

projects[views_bulk_operations][version] = "3.1"
projects[views_bulk_operations][type] = "module"
projects[views_bulk_operations][subdir] = "contrib"

projects[views_load_more][version] = "1.1"
projects[views_load_more][type] = "module"
projects[views_load_more][subdir] = "contrib"

projects[webform][version] = "4.0-beta1"
projects[webform][type] = "module"
projects[webform][subdir] = "contrib"

; Curate
projects[curate][version] = "1.0"
projects[curate][type] = "module"
projects[curate][subdir] = "contrib"
projects[curate][download][type] = "git"
projects[curate][download][branch] = "7.x-1.x"

projects[ckeditor][version] = "1.x-dev"
projects[ckeditor][type] = "module"
projects[ckeditor][subdir] = "contrib"
projects[ckeditor][download][type] = "git"
projects[ckeditor][download][revision] = "57245a9"
projects[ckeditor][download][branch] = "7.x-1.x"
; Integration with Media 2.x
; http://drupal.org/node/1504696
projects[ckeditor][patch][1504696] = "https://drupal.org/files/issues/ckeditor_1504696_77.patch"

projects[collections][version] = "1.x-dev"
projects[collections][type] = "module"
projects[collections][subdir] = "contrib"
projects[collections][download][type] = "git"
projects[collections][download][revision] = "b4e8212"
projects[collections][download][branch] = "7.x-1.x"

projects[date][version] = "2.6"
projects[date][type] = "module"
projects[date][subdir] = "contrib"

projects[edit][version] = "1.x-dev"
projects[edit][type] = "module"
projects[edit][subdir] = "contrib"
projects[edit][download][type] = "git"
projects[edit][download][revision] = "cf62974"
projects[edit][download][branch] = "7.x-1.x"
; Backport of Edit button for navbar
; http://drupal.org/node/1994256
projects[edit][patch][1994256] = "http://drupal.org/files/edit-navbar-button-1994256-15.patch"
; Edit Module fails for "psudeo" fields provided via Relationship or Appended
; Global Text in Views
; http://drupal.org/node/2015295
projects[edit][patch][2015295] = "http://drupal.org/files/edit-views-psuedo-fields-2015295-6.patch"

projects[file_entity][version] = "2.x-dev"
projects[file_entity][type] = "module"
projects[file_entity][subdir] = "contrib"
projects[file_entity][download][type] = "git"
projects[file_entity][download][revision] = "7b9d082"
projects[file_entity][download][branch] = "7.x-2.x"

projects[file_entity_link][version] = "1.0-alpha3"
projects[file_entity_link][type] = "module"
projects[file_entity_link][subdir] = "contrib"

projects[iib][version] = "1.x-dev"
projects[iib][type] = "module"
projects[iib][subdir] = "contrib"
projects[iib][download][type] = "git"
projects[iib][download][revision] = "17a55eb"
projects[iib][download][branch] = "7.x-1.x"
; UX Improvements
; http://drupal.org/node/1737036
projects[iib][patch][1737036] = "http://drupal.org/files/iib-entity-css-1737036-18.patch"

projects[linkit][version] = "2.6"
projects[linkit][type] = "module"
projects[linkit][subdir] = "contrib"

projects[media][version] = "2.x-dev"
projects[media][type] = "module"
projects[media][subdir] = "contrib"
projects[media][download][type] = "git"
projects[media][download][revision] = "814c34e"
projects[media][download][branch] = "7.x-2.x"

projects[media_youtube][version] = "2.x-dev"
projects[media_youtube][type] = "module"
projects[media_youtube][subdir] = "contrib"
projects[media_youtube][download][type] = "git"
projects[media_youtube][download][revision] = "acff0f6"
projects[media_youtube][download][branch] = "7.x-2.x"

projects[media_vimeo][version] = "2.x-dev"
projects[media_vimeo][type] = "module"
projects[media_vimeo][subdir] = "contrib"
projects[media_vimeo][download][type] = "git"
projects[media_vimeo][download][revision] = "26b2eee"
projects[media_vimeo][download][branch] = "7.x-2.x"

projects[nra][version] = "1.0-alpha2"
projects[nra][type] = "module"
projects[nra][subdir] = "contrib"

projects[sps][version] = "1.x-dev"
projects[sps][type] = "module"
projects[sps][subdir] = "contrib"
projects[sps][download][type] = "git"
projects[sps][download][revision] = "76e89f4"
projects[sps][download][branch] = "7.x-1.x"
; UX improvements on page level IIB
; http://drupal.org/node/1733490
projects[sps][patch][1733490] = "http://drupal.org/files/sps-css-cleanup-1733490-3.patch"
; SPS should not prevent other modules that use Entity API from working
; http://drupal.org/node/1934130
projects[sps][patch][1934130] = "http://drupal.org/files/sps-1934130-11.patch"

projects[timeago][version] = "2.x-dev"
projects[timeago][type] = "module"
projects[timeago][subdir] = "contrib"
projects[timeago][download][type] = "git"
projects[timeago][download][url] = "http://git.drupal.org/project/timeago.git"
projects[timeago][download][branch] = "7.x-2.x"
projects[timeago][download][revision] = "768ea66"
; Provide a dedicated date type
; http://drupal.org/node/1427226
projects[timeago][patch][1427226] = "http://drupal.org/files/1427226-timeago-date-type.patch"

projects[revision_scheduler][version] = "1.x-dev"
projects[revision_scheduler][type] = "module"
projects[revision_scheduler][subdir] = "contrib"
projects[revision_scheduler][download][type] = "git"
projects[revision_scheduler][download][revision] = "ab04410"
projects[revision_scheduler][download][branch] = "7.x-1.x"
; Notice: Undefined index: path i
; revision_scheduler_preprocess_menu_local_action()
; http://drupal.org/node/1564348
projects[revision_scheduler][download][branch] = "http://drupal.org/files/fixes-notice-issue-1564348.patch"

projects[workbench][version] = "1.x-dev"
projects[workbench][type] = "module"
projects[workbench][subdir] = "contrib"
projects[workbench][download][type] = "git"
projects[workbench][download][revision] = "6856e4a"
projects[workbench][download][branch] = "7.x-1.x"

projects[workbench_moderation][version] = "1.3"
projects[workbench_moderation][type] = "module"
projects[workbench_moderation][subdir] = "contrib"

projects[workbench_moderation_notes][version] = "1.x-dev"
projects[workbench_moderation_notes][type] = "module"
projects[workbench_moderation_notes][subdir] = "contrib"
projects[workbench_moderation_notes][download][type] = "git"
projects[workbench_moderation_notes][download][revision] = "8e5e6f4"
projects[workbench_moderation_notes][download][branch] = "7.x-1.x"

projects[xautoload][version] = "2.7"
projects[xautoload][type] = "module"
projects[xautoload][subdir] = "contrib"

; Import
projects[import][version] = "1.0"
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

; Libraries
libraries[backbone][download][type] = "get"
libraries[backbone][download][url] = "http://documentcloud.github.io/backbone/backbone-min.js"

libraries[ckeditor][download][type] = "get"
libraries[ckeditor][download][url] = "http://download.cksource.com/CKEditor/CKEditor/CKEditor%204.3%20Beta/ckeditor_4.3_beta_standard_all.zip"

libraries[json2][download][type] = "get"
libraries[json2][download][url] = "https://github.com/douglascrockford/JSON-js/blob/master/json2.js"

libraries[jsonpath][download][type] = "get"
libraries[jsonpath][download][url] = "https://jsonpath.googlecode.com/files/jsonpath-0.8.1.php"

libraries[respondjs][download][type] = "get"
libraries[respondjs][download][url] = "https://github.com/scottjehl/Respond/tarball/master"

libraries[timeago][download][type] = "get"
libraries[timeago][download][url] = "https://raw.github.com/rmm5t/jquery-timeago/v1.3.0/jquery.timeago.js"

libraries[underscore][download][type] = "get"
libraries[underscore][download][url] = "http://documentcloud.github.io/underscore/underscore-min.js"

; Themes
projects[demonstratie][version] = "1.x-dev"
projects[demonstratie][type] = "theme"
projects[demonstratie][subdir] = "contrib"
projects[demonstratie][download][type] = "git"
projects[demonstratie][download][branch] = "7.x-1.x"

projects[ember][version] = "2.x-dev"
projects[ember][type] = "theme"
projects[ember][subdir] = "contrib"
projects[ember][download][type] = "git"
projects[ember][download][branch] = "7.x-2.x"
