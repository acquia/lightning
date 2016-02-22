#!/bin/bash

# This script is to be used with Probo CI (http://probo.ci/) and the development process of the Bear install profile.
# It is meant to enable building Lightning from a git branch

PROFILE_NAME='lightning'
MAKE_FILE=''
SRC_DIR='/src'
DESTINATION='/var/www/html'

function checkMakeFile() {
  # Matches the following:
  #  projects[lightning]
  #  projects[] = 'lightning'
  #  projects[] = "lightning"
  #  projects[] = lightning
  #  projects[]=lightning
  if grep "\[$PROFILE_NAME\]" $1 > /dev/null || grep "['\"]\?$PROFILE_NAME['\"]\?$" $1 > /dev/null; then
      echo 'Error: Project should not be listed in make file.' >&2
      exit 1
  fi
  return
}

# Remove the destination directory since Drush cares about that now.
rm -r $DESTINATION

if [ "$SRC_DIR/$MAKE_FILE" != '' ] && [ -f "$SRC_DIR/$MAKE_FILE" ]; then
  checkMakeFile "$SRC_DIR/$MAKE_FILE"

  drush make "$SRC_DIR/$MAKE_FILE" $DESTINATION
elif [ -f "$SRC_DIR/drupal-org-core.make" ] && [ -f "$SRC_DIR/drupal-org.make" ]; then
  checkMakeFile "$SRC_DIR/drupal-org-core.make"
  checkMakeFile "$SRC_DIR/drupal-org.make"

  drush make "$SRC_DIR/drupal-org-core.make" $DESTINATION
  cd $SRC_DIR
  drush make "drupal-org.make" --contrib-destination=. --no-core . -y
  cd -
fi

if [ ! -d "$DESTINATION/profiles/$PROFILE_NAME" ]; then
  cp -r $SRC_DIR "$DESTINATION/profiles/$PROFILE_NAME"
else
  echo 'Error: Unable to copy profile to destination because it already exists.' >&2
  exit 1
fi
