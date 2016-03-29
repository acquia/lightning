#!/bin/sh

MAKEFILE='build-lightning.make'
CALLPATH=`dirname $0`
TARGET=$1
shift

if [ -d $TARGET ]; then
  yes "yes" | rm -rf $TARGET
fi

if [[ ! -z "$@" ]]; then
  echo "Running drush make with additional params: $@"
  drush make $CALLPATH/$MAKEFILE $TARGET $@
else
  drush make $CALLPATH/$MAKEFILE $TARGET --concurrency=5
fi
