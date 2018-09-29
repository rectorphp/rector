#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344)
set -x

BUILD_DESTINATION="../rector-prefixed-build"

# cleanup build
rm -rf $BUILD_DESTINATION
mkdir $BUILD_DESTINATION

# prefix current code to $BUILD_DESTINATION directory (see "scoper.inc.php" for settings)
vendor/bin/php-scoper add-prefix --no-interaction --output-dir=$BUILD_DESTINATION

# prefix namespace in *.yml, *.yaml and *.neon files
# but not in /config, since there is only Rector\ services and "class names" that are not prefixed
# ref https://unix.stackexchange.com/a/15309/74260
(find $BUILD_DESTINATION -path $BUILD_DESTINATION/config -prune -o -type f \( -name \*.yml -o -name \*.yaml -o -name \*.neon \) | xargs perl -pi -e 's/((?:\\{1,2}\w+|\w+\\{1,2})(?:\w+\\{0,2})+)/RectorPrefixed\\\1/g')

# un-prefix Rector files, so it's public API, in configs etc.
# e.g.
# -use RectorPrefixed\Rector\...
# +use Rector\...

# "sed" cdommand format help:
# s#<old-code>#<new-code>#g
# s#RectorPrefixed\\Rector#Rector#g
# "RectorPrefixed\Rector" => "Rector"
(find $BUILD_DESTINATION -type f | xargs sed -i 's/RectorPrefixed\\Rector/Rector/g')
(find $BUILD_DESTINATION -type f | xargs sed -i 's/RectorPrefixed\\\\Rector/Rector/g')

# unprefix container dump - see https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L897
(find $BUILD_DESTINATION -type f | xargs sed -i 's/use Symfony/use RectorPrefixed\\\\Symfony/g')

# for cases like: https://github.com/rectorphp/rector-prefixed/blob/6b690e46e54830a944618d3a2bf50a7c2bd13939/src/Bridge/Symfony/NodeAnalyzer/ControllerMethodAnalyzer.php#L16
# "'" ref https://stackoverflow.com/a/24509931/1348344
# "prune" ref https://stackoverflow.com/a/4210072/1348344
(find $BUILD_DESTINATION -path $BUILD_DESTINATION/vendor -prune -o -type f | xargs sed -i "s#'RectorPrefixed\\#'#g")
(find $BUILD_DESTINATION -path $BUILD_DESTINATION/vendor -prune -o -type f | xargs sed -i "s#'RectorPrefixed\\\\#'#g")

# Nette string validator
# callable|Nette\\DI\\Statement|array:1 => callable|RectorPrefixed\\Nette\\DI\\Statement|array:1
sed -i 's#|Nette\\\\DI#|RectorPrefixed\\\\Nette\\\\DI#g' $BUILD_DESTINATION/vendor/nette/di/src/DI/Compiler.php

# Symfony Bridge => keep Symfony classes

# RectorPrefixed\App\\Kernel => App\Kernel
sed -i 's#RectorPrefixed\\\\App\\\\Kernel#App\\Kernel#g' $BUILD_DESTINATION/packages/Symfony/src/Bridge/DefaultAnalyzedSymfonyApplicationContainer.php
# RectorPrefixed\Symfony\Component\HttpKernel\Kernel => Symfony\Component\HttpKernel\Kernel
(find $BUILD_DESTINATION/packages/Symfony/src/Bridge -type f | xargs sed -i 's#RectorPrefixed\\Symfony\\Component#Symfony\\Component#g')

# copy template files
cp composer.json $BUILD_DESTINATION/composer.json
cp bin/rector-prefixed/template/README.md $BUILD_DESTINATION/README.md
cp bin/rector-prefixed/template/.travis.yml $BUILD_DESTINATION/.travis.yml

# rebuild composer dump so the new prefixed namespaces are autoloaded
# the new "RectorPrefixed\" is taken into account thanks to /vendor/composer/installed.json file,
composer dump-autoload -d $BUILD_DESTINATION --no-dev

# make bin executable
chmod +x $BUILD_DESTINATION/bin/rector

# clear kernel cache to make use of this new one,
# #todo? maybe prefix this cache as well?
(find $BUILD_DESTINATION -type f | xargs sed -i 's#_rector_cache#_prefixed_rector_cache#g')
rm -rf /tmp/_prefixed_rector_cache

# build composer.json
BUILD_DESTINATION=$BUILD_DESTINATION bin/rector-prefixed/build-composer-json.php

# run it to test it
build/bin/rector
