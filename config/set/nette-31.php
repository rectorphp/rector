<?php

declare(strict_types=1);

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)->call('configure', [[
        RenameClassRector::OLD_TO_NEW_CLASSES => [
            // https://github.com/nette/application/compare/v3.0.7...v3.1.0
            'Nette\Application\IRouter' => 'Nette\Routing\Router',  // TODO not sure about this, it is not simple rename, Nette\Routing\Router already exists in nette/routing
            'Nette\Application\IResponse' => 'Nette\Application\Response',
            'Nette\Application\UI\IRenderable' => 'Nette\Application\UI\Renderable',
            'Nette\Application\UI\ISignalReceiver' => 'Nette\Application\UI\SignalReceiver',
            'Nette\Application\UI\IStatePersistent' => 'Nette\Application\UI\StatePersistent',
            'Nette\Application\UI\ITemplate' => 'Nette\Application\UI\Template',
            'Nette\Application\UI\ITemplateFactory' => 'Nette\Application\UI\TemplateFactory',
            'Nette\Bridges\ApplicationLatte\ILatteFactory' => 'Nette\Bridges\ApplicationLatte\LatteFactory',

            // https://github.com/nette/bootstrap/compare/v3.0.2...v3.1.0
            'Nette\Configurator' => 'Nette\Bootstrap\Configurator',

            // https://github.com/nette/caching/compare/v3.0.2...v3.1.0
            'Nette\Caching\IBulkReader' => 'Nette\Caching\BulkReader',
            'Nette\Caching\IStorage' => 'Nette\Caching\Storage',
            'Nette\Caching\Storages\IJournal' => 'Nette\Caching\Storages\Journal',

            // https://github.com/nette/database/compare/v3.0.7...v3.1.1
            'Nette\Database\ISupplementalDriver' => 'Nette\Database\Driver',
            'Nette\Database\IConventions' => 'Nette\Database\Conventions',
            'Nette\Database\Context' => 'Nette\Database\Explorer',
        ],
    ]]);

    // TODO not working now because of https://github.com/rectorphp/rector/issues/5171
    $services->set(ComposerModifier::class)
        ->call('configure', [
            ValueObjectInliner::inline([
                // meta package
                new ChangePackageVersion('nette/nette', '^3.1'),
                // https://github.com/nette/nette/blob/v3.0.0/composer.json vs https://github.com/nette/nette/blob/v3.1.0/composer.json
                new ChangePackageVersion('php', '^7.2'),
                new ChangePackageVersion('nette/application', '^3.1'),
                new ChangePackageVersion('nette/bootstrap', '^3.1'),
                new ChangePackageVersion('nette/caching', '^3.1'),
                new RemovePackage('nette/component-model'),
                new ChangePackageVersion('nette/database', '^3.1'),
                new ChangePackageVersion('nette/di', '^3.0'),
                new ChangePackageVersion('nette/finder', '^2.5'),
                new ChangePackageVersion('nette/forms', '^3.0'),
                new ChangePackageVersion('nette/http', '^3.1'),
                new ChangePackageVersion('nette/mail', '^3.1'),
                new RemovePackage('nette/neon'),
                new ChangePackageVersion('nette/php-generator', '^3.5'),
                new ChangePackageVersion('nette/robot-loader', '^3.3'),
                new ChangePackageVersion('nette/safe-stream', '^2.4'),
                new ChangePackageVersion('nette/security', '^3.1'),
                new ChangePackageVersion('nette/tokenizer', '^3.0'),
                new ChangePackageVersion('nette/utils', '^3.2'),
                new ChangePackageVersion('latte/latte', '^2.9'),
                new ChangePackageVersion('tracy/tracy', '^2.8'),
            ]),
        ]);
};
