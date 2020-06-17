#!/bin/bash

set -e

# Prepares a release branch.
# Example usage: ./release-branch 4.1.0

RELEASE_BRANCH=release/$1

# Ensure we are on a mainline release branch.
BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ $BRANCH =~ ^8\.x\-[0-9]+\.x$ ]]; then
  git pull
  git checkout -b $RELEASE_BRANCH

  composer update
  cp composer.lock tests/fixtures/$1.lock

  git add .
  git commit --message "$1 Release"
  git push --set-upstream origin $RELEASE_BRANCH
else
  echo "This can only be done from a mainline release branch, e.g. 8.x-4.x."
  exit 1
fi
