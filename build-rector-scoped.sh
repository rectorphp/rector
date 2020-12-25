#!/bin/sh -l

# local-prefix test

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u


# functions
note()
{
    MESSAGE=$1;

    printf "\n";
    echo "[NOTE] $MESSAGE";
    printf "\n";
}


note "Starts"

# configure here
NESTED_DIRECTORY="rector-nested"
SCOPED_DIRECTORY="rector-scoped"



# ---------------------------


note "Coping root files to $NESTED_DIRECTORY directory"
rsync -av * "$NESTED_DIRECTORY" --quiet

note "Running composer update without dev"
composer update --no-dev --no-progress --ansi --working-dir "$NESTED_DIRECTORY" # --ignore-platform-req php

# Unpacking PHPStan
note "Unpacking PHPStan"
wget https://github.com/box-project/box/releases/download/3.11.0/box.phar -N --no-verbose
php box.phar extract "$NESTED_DIRECTORY/vendor/phpstan/phpstan/phpstan.phar" "$NESTED_DIRECTORY/vendor/phpstan/phpstan-extracted"

# this will remove dependency on dev packages that are imported in phpstan.neon
rm -f "$NESTED_DIRECTORY/phpstan-for-rector.neon"

# Avoid Composer v2 platform checks (composer.json requires PHP 7.4+, but below we are running 7.3)
note "Disabling platform check"
composer config platform-check false

# 2. scope it
# @todo temporary only no net + is already locally insatlled
note "Running scoper to $SCOPED_DIRECTORY"
wget https://github.com/humbug/php-scoper/releases/download/0.14.0/php-scoper.phar -N --no-verbose

php php-scoper.phar add-prefix bin config packages rules src templates vendor composer.json --output-dir "../$SCOPED_DIRECTORY" --config scoper.php.inc --force --ansi --working-dir "$NESTED_DIRECTORY"

# keep only one PHPStan
rm -rf "$SCOPED_DIRECTORY/vendor/phpstan/phpstan"

note "Dumping Composer Autoload"
composer dump-autoload --working-dir "$SCOPED_DIRECTORY" --ansi --optimize --classmap-authoritative --no-dev

note "Scoping composer.json"
composer require symplify/package-scoper
vendor/bin/package-scoper scope-composer-json "$SCOPED_DIRECTORY/composer.json" --ansi

# clean up
rm -rf "$NESTED_DIRECTORY"


# copy metafiles needed for release
cp -R scoped/. "$SCOPED_DIRECTORY"
