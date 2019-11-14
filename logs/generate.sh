#!/bin/bash

for file in $(ls *.md); do
  # Strip the .md extension off to determine the git tag.
  tag="${file%.*}"

  # Get the touch-compatible time that the tag was created.
  time=$(git tag --list $tag --format="%(creatordate:format:%Y%m%d%H%M.%S)")

  if [ $time ]; then
    # Update the file's modification time.
    touch -t $time $file
  else
    # Use the current time.
    touch $file
  fi
done

for file in $(ls -t *.md); do
  if [[ -x $file ]]; then
    ./$file
  else
    cat $file
  fi
  echo
done
