#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344)
set -x

# install php-scoper if missing
# broken due to php-parser 3, see: https://github.com/humbug/php-scoper/pull/203
# if [ ! -d vendor/bin ]; then composer bin php-scoper require humbug/php-scoper; fi

# cleanup build
rm -rf build/

# prefix current code to /build directory (see "scoper.inc.php" for settings)
php php-scoper.phar add-prefix --no-interaction --prefix='RectorPrefixed'

# prefix namespace in *.yml files
(find build/ -type f -name '*.yml' | xargs perl -pi -e 's/((?:\\{1,2}\w+|\w+\\{1,2})(?:\w+\\{0,2})+)/RectorPrefixed\\\1/g')

# un-prefix Rector files, so it's public API, in configs etc.
(find build/ -type f | xargs sed -i 's/RectorPrefixed\\Rector/Rector/g')
(find build/ -type f | xargs sed -i 's/RectorPrefixed\\\\Rector/Rector/g')

# unprefix container dump - see https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L897
(find build/ -type f | xargs sed -i 's/use Symfony/use RectorPrefixed\\\\Symfony/g')

# ?todo
cp composer.json build/composer.json

# rebuild composer dump
composer dump-autoload -d build --no-dev

# make bin executable
chmod +x build/bin/rector

# clear kernel cache to make use of this new one, maybe prefix this cache as well?
rm -rf /tmp/_rector_cache

# run it to test it
build/bin/rector
