#!/bin/sh -l

# see https://stackoverflow.com/questions/66644233/how-to-propagate-colors-from-bash-script-to-github-action?noredirect=1#comment117811853_66644233
export TERM=xterm-color

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
BUILD_DIRECTORY=$1
RESULT_DIRECTORY=$2

# ---------------------------

note "Starts"

# this will remove dependency on dev packages that are imported in phpstan.neon
rm -f "$BUILD_DIRECTORY/phpstan-for-rector.neon"

# 2. scope it
note "Running scoper to $RESULT_DIRECTORY"
wget https://github.com/humbug/php-scoper/releases/download/0.14.0/php-scoper.phar -N --no-verbose

# Work around possible PHP memory limits
php -d memory_limit=-1 php-scoper.phar add-prefix bin config src packages rules vendor composer.json --output-dir "../$RESULT_DIRECTORY" --config scoper.php --force --ansi --working-dir "$BUILD_DIRECTORY"


note "Dumping Composer Autoload"
composer dump-autoload --working-dir "$RESULT_DIRECTORY" --ansi --optimize --classmap-authoritative --no-dev

rm -rf "$BUILD_DIRECTORY"


# copy metafiles needed for release
note "Copy metafiles like composer.json, .github etc to repository"
rm -f "$RESULT_DIRECTORY/composer.json"

# make bin/rector runnable without "php"
chmod 777 "$RESULT_DIRECTORY/bin/rector"
chmod 777 "$RESULT_DIRECTORY/bin/rector.php"

note "Finished"
