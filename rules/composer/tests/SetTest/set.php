<?php declare(strict_types=1);

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ComposerModifier::class)
        ->call('setFilePath', [__DIR__ . '/Fixture/composer_before.json'])
        ->call('configure', [ValueObjectInliner::inline([new ChangePackageVersion('nette/nette', '^3.0')])]);
};
