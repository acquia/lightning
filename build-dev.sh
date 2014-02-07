#!/bin/sh

MAKEFILE='build-lightning.make'
TARGET=$1
CALLPATH=`dirname $0`
drush make --working-copy --concurrency=5 $CALLPATH/$MAKEFILE $TARGET
