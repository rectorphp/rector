# local-prefix test

# repace phpstan/phpstan with phpstan/phpstan-src
rsync -av * rector-nested

composer remove phpstan/phpstan --no-update --working-dir rector-nested
composer config repositories.repo-name vcs https://github.com/phpstan/phpstan-src.git --working-dir rector-nested
composer require phpstan/phpstan-src:@dev --no-update --working-dir rector-nested

composer require jetbrains/phpstorm-stubs:dev-master#05d145c0bbafcf9a551fdd8824adb2a7e259fdaf --no-update --working-dir rector-nested
composer update --no-progress --ansi  --working-dir rector-nested

bin/rector process vendor/phpstan/phpstan-src/src --config ci/downgrade-phpstan-php74-rector.php --ansi

composer update --no-dev --prefer-dist --ansi --working-dir rector-nested

# 2. scope it
# @todo temporary only no net + is already locally insatlled
# wget https://github.com/humbug/php-scoper/releases/download/0.13.9/php-scoper.phar
cd rector-nested && php ../php-scoper.phar add-prefix bin config packages rules src templates vendor composer.json --output-dir ../rector-scoped --config scoper.inc.php --force --ansi -vvv
cd ..

# [note] ↓ not needed, platform > 7.2.4 in composer.json handles it
# composer config platform.php 7.2 --working-dir rector-scoped
# bin/rector process rector-scoped/vendor/composer/platform_check.php --config ci/downgrade-php72-rector-scoped.php

composer dump-autoload --working-dir rector-scoped --ansi --optimize --classmap-authoritative --no-dev

vendor/bin/package-scoper scope-composer-json rector-scoped/composer.json --ansi

# clean up
rm -rf rector-nested
