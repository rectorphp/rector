<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\Rector\RemovePackageComposerRector;
use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Composer\Rector\ChangePackageVersionComposerRector::class)->call('configure', [[\Rector\Composer\Rector\ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/nette', '^3.0'),
        // https://github.com/nette/nette/blob/v2.4.0/composer.json vs https://github.com/nette/nette/blob/v3.0.0/composer.json
        // older versions have security issues
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/application', '^3.0.6'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/bootstrap', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/caching', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/component-model', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/database', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/di', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/finder', '^2.5'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/forms', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/http', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/mail', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/neon', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/php-generator', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/robot-loader', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/safe-stream', '^2.4'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/security', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/tokenizer', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/utils', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('latte/latte', '^2.5'),
        new \Rector\Composer\ValueObject\PackageAndVersion('tracy/tracy', '^2.6'),
        // contributte packages
        new \Rector\Composer\ValueObject\PackageAndVersion('contributte/event-dispatcher-extra', '^0.8'),
        new \Rector\Composer\ValueObject\PackageAndVersion('contributte/forms-multiplier', '3.1.x-dev'),
        // other packages
        new \Rector\Composer\ValueObject\PackageAndVersion('radekdostal/nette-datetimepicker', '^3.0'),
    ])]]);
    $services->set(\Rector\Composer\Rector\RemovePackageComposerRector::class)->call('configure', [[\Rector\Composer\Rector\RemovePackageComposerRector::PACKAGE_NAMES => ['nette/deprecated', 'nette/reflection']]]);
    $services->set(\Rector\Composer\Rector\ReplacePackageAndVersionComposerRector::class)->call('configure', [[\Rector\Composer\Rector\ReplacePackageAndVersionComposerRector::REPLACE_PACKAGES_AND_VERSIONS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        // webchemistry to contributte
        new \Rector\Composer\ValueObject\ReplacePackageAndVersion('webchemistry/forms-multiplier', 'contributte/forms-multiplier', '3.1.x-dev'),
    ])]]);
};
