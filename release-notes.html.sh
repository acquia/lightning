#!/bin/bash

MARKDOWN=`command -v markdown`

if [[ $MARKDOWN ]]; then
  FILE=/tmp/release-notes.md

  ./release-notes.md.sh > $FILE
  $MARKDOWN $FILE
  rm $FILE
else
  echo "markdown command not found. Install markdown-to-html from https://github.com/cwjohan/markdown-to-html."
fi
