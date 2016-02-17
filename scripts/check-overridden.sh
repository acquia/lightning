#!/bin/bash

: ${DRUSH:=drush}
: ${DRUSH_ARGS:=}

LIGHTNING_FEATURES="lightning_admin lightning_article lightning_base lightning_blocks lightning_content lightning_featured lightning_files lightning_filter lightning_forms lightning_fpp lightning_identifiers lightning_image lightning_landing lightning_media lightning_metatags lightning_moderation lightning_page lightning_panels lightning_roles lightning_sps lightning_theme lightning_views lightning_wysiwyg"

# TODO: We should make sure that 'diff' is downloaded first!
$DRUSH $DRUSH_ARGS en -y diff

# Notify which features are overridden.
for lightning_feature in $LIGHTNING_FEATURES; do
  echo "Checking $lightning_feature..."
  if $DRUSH $DRUSH_ARGS features-diff $lightning_feature 2>&1 | grep -v 'Feature is in its default state'; then
    echo "$lightning_feature is overridden."
  fi
done

exit 0
