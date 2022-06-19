#!/usr/bin/env bash

# usage:
#
#   export PHP72_BIN_PATH=/opt/homebrew/Cellar/php@7.2/7.2.34_4/bin/php && sh ./full_build.sh

# see https://stackoverflow.com/questions/66644233/how-to-propagate-colors-from-bash-script-to-github-action?noredirect=1#comment117811853_66644233
export TERM=xterm-color

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u

# clean up
rm -rf rector-prefixed-downgraded
rm -rf composer.lock
rm -rf vendor
composer clear-cache

composer install --ansi

# ensure clear cache
bin/rector --version --clear-cache

# ensure remove cache directory
php -r 'shell_exec("rm -rf " . sys_get_temp_dir() . "/rector_cached_files");';

composer install --no-dev --ansi

# early downgrade individual functions
bin/rector process src/functions vendor/symfony/string/Resources/functions.php -c build/config/config-downgrade.php --ansi

rsync --exclude rector-build -av * rector-build --quiet

rm -rf rector-build/packages-tests rector-build/rules-tests rector-build/tests rector-build/bin/generate-changelog.php rector-build/bin/validate-phpstan-version.php rector-build/bin/clean-phpstan.php rector-build/vendor/tracy/tracy/examples rector-build/vendor/symfony/console/Tester rector-build/vendor/symfony/console/Event rector-build/vendor/symfony/console/EventListener rector-build/vendor/symfony/contracts/Cache/ItemInterface.php rector-build/vendor/symfony/dependency-injection/ExpressionLanguageProvider.php rector-build/vendor/symfony/dependency-injection/ExpressionLanguage.php rector-build/vendor/tracy/tracy/examples rector-build/vendor/tracy/tracy/src/Bridges rector-build/vendor/tracy/tracy/src/Tracy/Bar rector-build/vendor/tracy/tracy/src/Tracy/Session

php -d memory_limit=-1 bin/rector process rector-build/bin rector-build/config rector-build/src rector-build/packages rector-build/rules rector-build/vendor --config build/config/config-downgrade.php --ansi --no-diffs

sh build/build-rector-scoped.sh rector-build rector-prefixed-downgraded

# verify syntax valid in php 7.2
composer global require php-parallel-lint/php-parallel-lint

if test -z ${PHP72_BIN_PATH+y}; then
    ~/.composer/vendor/bin/parallel-lint rector-prefixed-downgraded --exclude rector-prefixed-downgraded/stubs --exclude rector-prefixed-downgraded/vendor/tracy/tracy/examples --exclude rector-prefixed-downgraded/vendor/rector/rector-generator/templates --exclude rector-prefixed-downgraded/vendor/symfony/contracts/Cache --exclude rector-prefixed-downgraded/vendor/symfony/contracts/HttpClient/Test;
else
    echo "verify syntax valid in php 7.2 with specify PHP72_BIN_PATH env";
    $PHP72_BIN_PATH ~/.composer/vendor/bin/parallel-lint rector-prefixed-downgraded --exclude rector-prefixed-downgraded/stubs --exclude rector-prefixed-downgraded/vendor/tracy/tracy/examples --exclude rector-prefixed-downgraded/vendor/rector/rector-generator/templates --exclude rector-prefixed-downgraded/vendor/symfony/contracts/Cache --exclude rector-prefixed-downgraded/vendor/symfony/contracts/HttpClient/Test;
fi

# Check php 7.2 can be used locally with PHP72_BIN_PATH env
# rector-prefixed-downgraded check
cp -R build/target-repository/. rector-prefixed-downgraded
cp -R templates rector-prefixed-downgraded/
cp CONTRIBUTING.md rector-prefixed-downgraded/
cp preload.php rector-prefixed-downgraded/

# rector-build check
cd rector-prefixed-downgraded

if test -z ${PHP72_BIN_PATH+y}; then
    bin/rector list --ansi;
else
    echo "verify scoped rector with specify PHP72_BIN_PATH env";
    $PHP72_BIN_PATH bin/rector list --ansi;
fi

cd ..

rm -rf rector-prefixed-downgraded

# rollback early change of src/functions
git checkout src/functions

# back to get dev dependencies
composer install --ansi

# remove php-parallel-lint from global dependencies
composer global remove php-parallel-lint/php-parallel-lint
