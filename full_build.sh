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

composer install --no-dev --ansi

rsync --exclude rector-build -av * rector-build --quiet

rm -rf rector-build/packages-tests rector-build/rules-tests rector-build/tests rector-build/bin/generate-changelog.php rector-build/bin/validate-phpstan-version.php

sh build/downgrade-rector.sh rector-build

cd rector-build

# avoid syntax error in php 7.1 and 7.2
rm rector.php

cp ../build/target-repository/bootstrap.php .
cp ../preload.php .

# Check php 7.1 can be used locally with PHP71_BIN_PATH env
# Prefixing build only works on php < 8.0, can be used locally with PHP80_BIN_PATH env

#
# usage:
#
#   export PHP71_BIN_PATH=/opt/homebrew/Cellar/php@7.1/7.1.33_4/bin/php PHP80_BIN_PATH=/opt/homebrew/Cellar/php@8.0/8.0.14/bin/php && sh ./full_build.sh
#
if test -z ${PHP71_BIN_PATH+y}; then
    bin/rector list --ansi;
else
    echo "verify downgraded rector with specify PHP71_BIN_PATH env";
    $PHP71_BIN_PATH bin/rector list --ansi;
fi

cd ..

# We may need a way to specify PHP path on run build/build-rector-scoped.sh, eg:
#
#     /path/to/php/bin/php build/build-rector-scoped.sh rector-build rector-prefixed-downgraded
#
#
sh build/build-rector-scoped.sh rector-build rector-prefixed-downgraded
cd rector-prefixed-downgraded
cp ../build/target-repository/bootstrap.php .
cp ../preload.php .

if test -z ${PHP71_BIN_PATH+y}; then
    bin/rector list --ansi;
    bin/rector process vendor/symfony/string/Slugger/ --dry-run;
else
    echo "verify scoped rector with specify PHP71_BIN_PATH env";
    $PHP71_BIN_PATH bin/rector list --ansi;
    $PHP71_BIN_PATH bin/rector process vendor/symfony/string/Slugger/ --dry-run;
fi

cd ..

rm -rf rector-prefixed-downgraded
rm -rf rector-build