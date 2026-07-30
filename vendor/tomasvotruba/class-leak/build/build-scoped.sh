#!/usr/bin/env bash

# inspired from https://github.com/rectorphp/rector/blob/main/build/build-rector-scoped.sh

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
    echo "\033[0;33m[NOTE] $MESSAGE\033[0m";
}


# configure here
BUILD_DIRECTORY=$1
RESULT_DIRECTORY=$2

# ---------------------------

note "Starts"

# 2. scope it
note "Running scoper with '$RESULT_DIRECTORY' output directory"
wget https://github.com/humbug/php-scoper/releases/download/0.17.5/php-scoper.phar -N --no-verbose

# create directory
mkdir "$RESULT_DIRECTORY" -p

# Work around possible PHP memory limits
php -d memory_limit=-1 php-scoper.phar add-prefix src bin vendor composer.json --output-dir "../$RESULT_DIRECTORY" --config scoper.php --force --ansi --working-dir "$BUILD_DIRECTORY"

note "Show prefixed files in '$RESULT_DIRECTORY'"
ls -l $RESULT_DIRECTORY

note "Dumping Composer Autoload"
composer dump-autoload --working-dir "$RESULT_DIRECTORY" --ansi --classmap-authoritative --no-dev

# make bin/class-leak runnable without "php"
chmod 777 "$RESULT_DIRECTORY/bin/class-leak"
chmod 777 "$RESULT_DIRECTORY/bin/class-leak.php"

note "Finished"
