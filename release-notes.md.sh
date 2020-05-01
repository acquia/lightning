#!/bin/bash

BRANCH=$(git rev-parse --abbrev-ref HEAD)
VERSION=$(git tag --list 5.* --sort -creatordate | head -n 1)
CHANGE_LOG=logs/$VERSION.md

if [[ ! -f $CHANGE_LOG ]]; then
  echo "Cannot generate release notes because $CHANGE_LOG does not exist."
  exit 1
fi

cat << 'EOF'
### Install
For the best developer experience and dependency management, install with Composer using the following command: `composer create-project acquia/lightning-project MYPROJECT`

### Changelog
EOF

if [[ -x $CHANGE_LOG ]]; then
  ./$CHANGE_LOG
else
  tail -n +2 $CHANGE_LOG | cat
fi
echo

cat << 'EOF'
### Update steps
Update your codebase:

```
composer self-update
EOF

echo "composer require acquia/lightning:~$VERSION --no-update"

cat << 'EOF'
composer update
```

Run database updates (if required - depending on the version from which you are updating):

```
drush cache:rebuild
drush updatedb
```

Run Lightning configuration updates (if required - depending on the version from which you are updating):

```
drush cache:rebuild
drush update:lightning
```
EOF

echo
echo "For general update instructions see https://github.com/acquia/lightning/blob/$BRANCH/UPDATE.md."
