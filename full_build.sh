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

cd rector-build

# avoid syntax error in php 7.1 and 7.2
rm rector.php

cp ../build/target-repository/bootstrap.php .
cp ../preload.php .

# allow to specify PHP71_BIN_PATH env
# usage:
#
#   export PHP71_BIN_PATH=/opt/homebrew/Cellar/php@7.1/7.1.33_4/bin/php && sh ./full_build.sh
#
if [ -z "$PHP71_BIN_PATH" ]; then
    eval "bin/rector list --ansi";
else
    eval "$PHP71_BIN_PATH bin/rector list --ansi";
fi

cd ..
rm -rf rector-build

# Prefixed build only works on PHP < 8.1 now, so only able to run on CI.
# We may need a way to specify PHP path on run build/build-rector-scoped.sh, eg:
#
#     /path/to/php/bin/php build/build-rector-scoped.sh rector-build rector-prefixed-downgraded
#
#
# sh build/build-rector-scoped.sh rector-build rector-prefixed-downgraded
# cd rector-prefixed-downgraded
# cp ../build/target-repository/bootstrap.php .
# cp ../preload.php .
# bin/rector list --ansi
# bin/rector process vendor/symfony/string/Slugger/ --dry-run

# cd ..
# rm -rf rector-prefixed-downgraded
