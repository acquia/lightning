#!/bin/sh
CI_BRANCH=`git symbolic-ref --short HEAD`
echo "projects: {lightning: {download: {branch: $CI_BRANCH, url: https://github.com/$TRAVIS_REPO_SLUG.git}}}" >> env.yml
