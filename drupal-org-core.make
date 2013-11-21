; A separate drupal-org-core.make file makes it so we can apply core patches
; if we need to.

api = 2
core = 7.x
projects[drupal][type] = core
projects[drupal][version] = 7.24

; CORE PATCHES

; Allow install profiles to change the system requirements
; http://drupal.org/node/1772316
projects[drupal][patch][] = "http://drupal.org/files/drupal-7.x-allow_profile_change_sys_req-1772316-21.patch"

; Site Preview System
; Load multiple revisions at once - http://drupal.org/node/1730874
projects[drupal][patch][] = http://drupal.org/files/1730874_0.patch

; Permissions/Features
; user_role_grant_permissions() throws PDOException when used for a disabled
; module's permission or with non-existent permissions
; http://drupal.org/comment/7285420#comment-7285420
projects[drupal][patch][] = "http://drupal.org/files/drupal-fix_pdoexception_grant_permissions-737816-36-do-not-test.patch"
