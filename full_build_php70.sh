#!/usr/bin/env bash

# see https://stackoverflow.com/questions/66644233/how-to-propagate-colors-from-bash-script-to-github-action?noredirect=1#comment117811853_66644233
export TERM=xterm-color

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u


rm -rf vendor
rm -rf composer.lock

composer remove composer/xdebug-handler
composer require composer/composer
composer require composer/xdebug-handler:"2.0.1 as 1.4.6"
composer install --no-dev --ansi

bin/rector process vendor/symfony/console/Event/ConsoleCommandEvent.php -c build/config/config-downgrade-php70.php --ansi
bin/rector process vendor/symfony/console/EventListener/ErrorListener.php -c build/config/config-downgrade-php70.php --ansi

wget https://github.com/box-project/box/releases/download/3.12.2/box.phar -N --no-verbose
php box.phar extract vendor/phpstan/phpstan/phpstan.phar vendor/phpstan/phpstan-extracted

cp vendor/symfony/console/Event/ConsoleCommandEvent.php vendor/phpstan/phpstan-extracted/vendor/symfony/console/Event/ConsoleCommandEvent.php
cp vendor/symfony/console/EventListener/ErrorListener.php vendor/phpstan/phpstan-extracted/vendor/symfony/console/EventListener/ErrorListener.php

rm -rf vendor/bin/phpstan vendor/phpstan/phpstan/bootstrap.php
echo "<?php " > vendor/phpstan/phpstan/bootstrap.php
rm -rf vendor/phpstan/phpstan/phpstan.phar
rm -rf phpstan-for-rector.neon

rsync --exclude rector-build-php70 -av * rector-build-php70 --quiet
rm -rf rector-build-php70/packages-tests rector-build-php70/rules-tests rector-build-php70/tests

bin/rector list --debug

sh build/downgrade-rector-php70.sh rector-build-php70

git checkout phpstan-for-rector.neon
git checkout composer.json

sh build/build-rector-scoped-php70.sh rector-build-php70 rector-prefixed-downgraded-php70
bin/rector process rector-prefixed-downgraded-php70/vendor/phpstan/phpstan-extracted/src/Command/IgnoredRegexValidator.php -c build/config/config-downgrade-php70.php --ansi
bin/rector process build/target-repository-php70/bootstrap.php -c build/config/config-downgrade-php70.php --ansi

rm -rf php-parallel-lint
mv vendor vendor-backup

rm -rf rector-build-php70

/opt/homebrew/opt/php@7.0/bin/php /usr/local/bin/composer create-project php-parallel-lint/php-parallel-lint php-parallel-lint --ansi
/opt/homebrew/opt/php@7.0/bin/php php-parallel-lint/parallel-lint rector-prefixed-downgraded-php70 --exclude rector-prefixed-downgraded-php70/stubs --exclude rector-prefixed-downgraded-php70/vendor/symfony/error-handler/Resources --exclude rector-prefixed-downgraded-php70/vendor/symfony/http-kernel/Resources --exclude rector-prefixed-downgraded-php70/vendor/rector/rector-nette/tests --exclude rector-prefixed-downgraded-php70/vendor/symfony/polyfill-mbstring/bootstrap80.php --exclude rector-prefixed-downgraded-php70/vendor/tracy/tracy/examples --exclude rector-prefixed-downgraded-php70/vendor/rector/rector-installer/tests --exclude rector-prefixed-downgraded-php70/vendor/symplify/smart-file-system/tests --exclude rector-prefixed-downgraded-php70/vendor/symfony/http-foundation/Session --exclude rector-prefixed-downgraded-php70/vendor/symfony/var-dumper --exclude rector-prefixed-downgraded-php70/vendor/nette/caching --exclude rector-prefixed-downgraded-php70/vendor/rector/rector-nette/src/Rector/LNumber --exclude rector-prefixed-downgraded-php70/vendor/symfony/http-foundation/Test --exclude rector-prefixed-downgraded-php70/vendor/symplify/simple-php-doc-parser/tests --exclude rector-prefixed-downgraded-php70/vendor/tracy/tracy/src/Tracy/Bar/panels/info.panel.phtml --exclude rector-prefixed-downgraded-php70/vendor/rector/extension-installer/tests --exclude rector-prefixed-downgraded-php70/vendor/symfony/string/Slugger/AsciiSlugger.php --exclude rector-prefixed-downgraded-php70/vendor/ssch/typo3-rector/templates/maker

mv vendor-backup vendor
cp -R build/target-repository-php70/. rector-prefixed-downgraded-php70
cp -R templates rector-prefixed-downgraded-php70/

rm -rf rector-prefixed-downgraded-php70
