#!/usr/bin/env bash

# see https://stackoverflow.com/questions/66644233/how-to-propagate-colors-from-bash-script-to-github-action?noredirect=1#comment117811853_66644233
export TERM=xterm-color

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u


# configure - 1st argument, use like
# sh build/downgrade-rector.sh <directory-with-code-to-downgrade>
BUILD_DIRECTORY=$1

#---------------------------------------------

# 1. downgrade it
echo "[NOTE] Running downgrade in '$BUILD_DIRECTORY' directory\n";

# 2. debug downgrade paths
bin/rector downgrade-paths --config build/config/config-downgrade.php --working-dir $BUILD_DIRECTORY --ansi

# 3. provide directories to downgrade; includes the rector dirs
directories=$(php bin/rector downgrade-paths --config build/config/config-downgrade.php --working-dir $BUILD_DIRECTORY --ansi)
# experimental

# split array see https://stackoverflow.com/a/1407098/1348344
export IFS=";"

# 4. downgrade the directories
for directory in $directories; do
    echo "[NOTE] Downgrading '$directory' directory\n"

    # --working-dir is needed, so "SKIP" parameter is applied in absolute path of nested directory
    php -d memory_limit=-1 bin/rector process $directory --config build/config/config-downgrade.php --working-dir $BUILD_DIRECTORY --ansi
done


# CONFIRMED: give time to print all the files, before the next process takes over newly printed content
# avoids bugs like these half of files done, next half waiting https://github.com/rectorphp/rector-src/runs/2565478682
sleep 25
