#!/usr/bin/env bash

BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ $BRANCH != release/* ]]; then
    echo "Fixtures can only be generated on branches of the format 'release/n.n.n'."
    exit
fi

VERSION=${BRANCH#release/}

./install-drupal.sh

# Destroy the database.
drush sql:drop --yes

# Import the fixture from which to update.
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
