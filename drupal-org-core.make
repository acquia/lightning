; A separate drupal-org-core.make file for core patches.

api = 2
core = 7.x
projects[drupal][type] = core
projects[drupal][version] = 7.39

; CORE PATCHES

; Allow install profiles to change the system requirements
; http://drupal.org/node/1772316
projects[drupal][patch][] = "http://drupal.org/files/drupal-7.x-allow_profile_change_sys_req-1772316-21.patch"

; Load multiple revisions at once
; http://drupal.org/node/1730874
projects[drupal][patch][] = http://drupal.org/files/1730874_0.patch

; Registry rebuild should not parse the same file twice in the same request
; http://drupal.org/node/1470656
projects[drupal][patch][] = "http://drupal.org/files/drupal-1470656-14.patch"

; user_role_grant_permissions() throws PDOException when used for a disabled
; module's permission or with non-existent permissions
; http://drupal.org/comment/7285420#comment-7285420
projects[drupal][patch][] = "http://drupal.org/files/drupal-fix_pdoexception_grant_permissions-737816-36-do-not-test.patch"

; image_get_info always populates file_size, even if already set.
; http://drupal.org/node/2289493
projects[drupal][patch][] = "http://drupal.org/files/issues/drupal-2289493-3-image_get_info-filesize-D7.patch"

; Remove all occurences of sourceMappingURL and sourceURL
; when JS files are aggregated
; http://drupal.org/node/2400287
projects[drupal][patch][] = "http://drupal.org/files/issues/Issue-2400287-by-hass-Remove-JS-source-and-source-map-D7_0.patch"

; Pass $page_callback_result through hook_page_delivery_callback_alter().
; http://drupal.org/node/897504
projects[drupal][patch][] = "http://drupal.org/files/issues/pass-page-callback-result-897504-2.patch"

; Xss filter() ignores malicious content in data-attributes and mangles image captions.
; http://drupal.org/node/2105841
projects[drupal][patch][] = "http://drupal.org/files/issues/do-2105841_no_protocol_filter-90.patch"
