#!/bin/bash

# Prepares a release branch.
# Example usage: ./release-branch 4.1.0

RELEASE_BRANCH=release/$1
CHANGE_LOG=logs/$1.md

if [[ ! -f $CHANGE_LOG ]]; then
  echo "$CHANGE_LOG must exist before creating a release branch."
  exit 1
fi

read -p "Are the update instructions current? (y/n)" choice
if [[ "$choice" = "n" ]]; then
  echo "Go fix that and try again."
  exit 0
fi

# Ensure we are on a mainline release branch.
BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ $BRANCH =~ ^8\.x\-[0-9]+\.x$ ]]; then
  git pull
  git checkout -b $RELEASE_BRANCH

  cd logs
  ./generate.sh > ../CHANGELOG.md
  cd ..

  composer require --no-update drupal/lightning_core drupal/lightning_api drupal/lightning_layout drupal/lightning_media drupal/lightning_workflow
  composer update
  ./make-fixture.sh $1

  git add .
  git commit --message "$1 Release"
  git push --set-upstream origin $RELEASE_BRANCH
else
  echo "This can only be done from a mainline release branch, e.g. 8.x-4.x."
  exit 1
fi
