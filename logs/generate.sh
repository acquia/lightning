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

# List all the .md files, ordered by modification time, and
# cat each one, followed by an empty line. Finally, delete
# the last line of the output, since it's blank.
ls -t *.md | xargs -I{} sh -c "cat {}; echo" | sed '$ d'
