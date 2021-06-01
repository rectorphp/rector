#!/usr/bin/env bash

# see https://stackoverflow.com/questions/66644233/how-to-propagate-colors-from-bash-script-to-github-action?noredirect=1#comment117811853_66644233
export TERM=xterm-color

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u




composer install --no-dev --ansi

# early downgrade individual files of symfony Attribute classes
bin/rector process vendor/symfony/dependency-injection/Attribute/Autoconfigure.php -c build/config/config-downgrade-php70.php --ansi
bin/rector process vendor/symfony/dependency-injection/Attribute/AutoconfigureTag.php -c build/config/config-downgrade-php70.php --ansi

rsync --exclude rector-build -av * rector-build --quiet
rm -rf rector-build/packages-tests rector-build/rules-tests rector-build/tests

sh build/downgrade-rector.sh rector-build
sh build/build-rector-scoped.sh rector-build rector-prefixed-downgraded
