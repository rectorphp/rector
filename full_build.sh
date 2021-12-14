#!/usr/bin/env bash

# see https://stackoverflow.com/questions/66644233/how-to-propagate-colors-from-bash-script-to-github-action?noredirect=1#comment117811853_66644233
export TERM=xterm-color

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u

# clean up
rm -rf rector-build
rm -rf rector-prefixed-downgraded
rm -rf composer.lock
rm -rf vendor
composer clear-cache

# somehow needed now when downgrading ssch/typo3-rector
composer require psr/http-message
composer update --no-dev --ansi

rsync --exclude rector-build -av * rector-build --quiet

# back to original composer.json
git checkout composer.json

rm -rf rector-build/packages-tests rector-build/rules-tests rector-build/tests rector-build/bin/generate-changelog.php rector-build/bin/validate-phpstan-version.php

sh build/downgrade-rector.sh rector-build
sh build/build-rector-scoped.sh rector-build rector-prefixed-downgraded

cd rector-prefixed-downgraded
cp ../build/target-repository/bootstrap.php .
cp ../preload.php .
bin/rector list --ansi
bin/rector process vendor/symfony/string/Slugger/ --dry-run

cd ..
rm -rf rector-prefixed-downgraded