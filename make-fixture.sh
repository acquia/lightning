#!/usr/bin/env bash

BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ $BRANCH != release/* ]]; then
    echo "Fixtures can only be generated on branches of the format 'release/n.n.n'."
    exit
fi

VERSION=${BRANCH#release/}
# Ask git for the most recent semantic version tag, and use it as the version
# from which to update.
# FROM=$(git tag --list "4.*" --sort -creatordate | head -n 1)

./install-drupal.sh

# Destroy the database and import the fixture from which to update.
echo "Replacing database with $FROM snapshot..."
drush sql:drop --yes
cd docroot
php core/scripts/db-tools.php import ../tests/fixtures/$FROM.php.gz
cd ..

# Run updates.
drush updatedb --yes
drush update:lightning --no-interaction

# Export the database.
cd docroot
php core/scripts/db-tools.php dump-database-d8-mysql --schema-only='' | gzip -9 > ../tests/fixtures/$VERSION.php.gz
cd ..
