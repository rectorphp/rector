<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionRector;
use Rector\Composer\Rector\RemovePackageRector;
use Rector\Composer\ValueObject\PackageAndVersion;
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

            // https://github.com/nette/forms/compare/v3.0.7...v3.1.0-RC2
            'Nette\Forms\IControl' => 'Nette\Forms\Control',
            'Nette\Forms\IFormRenderer' => 'Nette\Forms\FormRenderer',
            'Nette\Forms\ISubmitterControl' => 'Nette\Forms\SubmitterControl',

            // https://github.com/nette/mail/compare/v3.0.1...v3.1.5
            'Nette\Mail\IMailer' => 'Nette\Mail\Mailer',

            // https://github.com/nette/security/compare/v3.0.5...v3.1.2
            'Nette\Security\IAuthorizator' => 'Nette\Security\Authorizator',
            'Nette\Security\IResource' => 'Nette\Security\Resource',
            'Nette\Security\IRole' => 'Nette\Security\Role',

            // https://github.com/nette/utils/compare/v3.1.4...v3.2.1
            'Nette\Utils\IHtmlString' => 'Nette\HtmlStringable',
            'Nette\Localization\ITranslator' => 'Nette\Localization\Translator',

            // https://github.com/nette/latte/compare/v2.5.5...v2.9.2
            'Latte\ILoader' => 'Latte\Loader',
            'Latte\IMacro' => 'Latte\Macro',
            'Latte\Runtime\IHtmlString' => 'Latte\Runtime\HtmlStringable',
            'Latte\Runtime\ISnippetBridge' => 'Latte\Runtime\SnippetBridge',
        ],
    ]]);

    $services->set(ChangePackageVersionRector::class)
        ->call('configure', [[
            ChangePackageVersionRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                // meta package
                new PackageAndVersion('nette/nette', '^3.1'),
                // https://github.com/nette/nette/blob/v3.0.0/composer.json vs https://github.com/nette/nette/blob/v3.1.0/composer.json
                new PackageAndVersion('nette/application', '^3.1'),
                new PackageAndVersion('nette/bootstrap', '^3.1'),
                new PackageAndVersion('nette/caching', '^3.1'),
                new PackageAndVersion('nette/database', '^3.1'),
                new PackageAndVersion('nette/di', '^3.0'),
                new PackageAndVersion('nette/finder', '^2.5'),
                new PackageAndVersion('nette/forms', '3.1.0-RC2'),  // TODO change whene 3.1 will be released
                new PackageAndVersion('nette/http', '^3.1'),
                new PackageAndVersion('nette/mail', '^3.1'),
                new PackageAndVersion('nette/php-generator', '^3.5'),
                new PackageAndVersion('nette/robot-loader', '^3.3'),
                new PackageAndVersion('nette/safe-stream', '^2.4'),
                new PackageAndVersion('nette/security', '^3.1'),
                new PackageAndVersion('nette/tokenizer', '^3.0'),
                new PackageAndVersion('nette/utils', '^3.2'),
                new PackageAndVersion('latte/latte', '^2.9'),
                new PackageAndVersion('tracy/tracy', '^2.8'),
            ]),
        ]]);

    $services->set(RemovePackageRector::class)
        ->call('configure', [[
            RemovePackageRector::PACKAGE_NAMES => [
                'nette/component-model',
                'nette/neon'
            ],
        ]]);
};
