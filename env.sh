#!/bin/sh
echo -e "projects:{lightning:{download:{branch: $TRAVIS_BRANCH, url: https://github.com/$TRAVIS_REPO_SLUG.git}}}" >> env.yml
