#!/bin/sh -l

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


# configure here
NESTED_DIRECTORY="rector-nested"
SCOPED_DIRECTORY="rector-scoped"

# ---------------------------

note "Starts"

note "Coping root files to $NESTED_DIRECTORY directory"
rsync -av * "$NESTED_DIRECTORY" --quiet


note "Running composer update without dev"
composer update --no-dev --no-progress --ansi --working-dir "$NESTED_DIRECTORY"

# this will remove dependency on dev packages that are imported in phpstan.neon
rm -f "$NESTED_DIRECTORY/phpstan-for-rector.neon"

# Avoid Composer v2 platform checks (composer.json requires PHP 7.4+, but below we are running 7.3)
note "Disabling platform check"
composer config platform-check false

# 2. scope it
note "Running scoper to $SCOPED_DIRECTORY"
wget https://github.com/humbug/php-scoper/releases/download/0.14.0/php-scoper.phar -N --no-verbose

php php-scoper.phar add-prefix bin config packages rules src templates vendor composer.json --output-dir "../$SCOPED_DIRECTORY" --config scoper.php --force --ansi --working-dir "$NESTED_DIRECTORY"


note "Dumping Composer Autoload"
composer dump-autoload --working-dir "$SCOPED_DIRECTORY" --ansi --optimize --classmap-authoritative --no-dev

rm -rf "$NESTED_DIRECTORY"


# copy metafiles needed for release
note "Copy metafiles like composer.json, .github etc to repository"
rm -f "$SCOPED_DIRECTORY/composer.json"
cp -R scoped/. "$SCOPED_DIRECTORY"

# make bin/rector runnable without "php"
chmod 777 "$SCOPED_DIRECTORY/bin/rector"
chmod 777 "$SCOPED_DIRECTORY/bin/rector.php"

note "Finished"
