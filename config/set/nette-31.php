<?php

declare(strict_types=1);

use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\Rector\RemovePackageComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CallableInMethodCallToVariableRector::class)
        ->call('configure', [[
            // see https://github.com/nette/caching/commit/5ffe263752af5ccf3866a28305e7b2669ab4da82
            CallableInMethodCallToVariableRector::CALLABLE_IN_METHOD_CALL_TO_VARIABLE => ValueObjectInliner::inline([
                new CallableInMethodCallToVariable('Nette\Caching\Cache', 'save', 1),
            ]),
        ]]);

    $services->set(RenameClassRector::class)->call('configure', [[
        RenameClassRector::OLD_TO_NEW_CLASSES => [
            'Nette\Bridges\ApplicationLatte\Template' => 'Nette\Bridges\ApplicationLatte\DefaultTemplate',

            // https://github.com/nette/application/compare/v3.0.7...v3.1.0
            'Nette\Application\IRouter' => 'Nette\Routing\Router',
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
            'Nette\Database\IRow' => 'Nette\Database\Row',
            'Nette\Database\IRowContainer' => 'Nette\Database\ResultSet',
            'Nette\Database\Table\IRow' => 'Nette\Database\Table\ActiveRow',
            'Nette\Database\Table\IRowContainer' => 'Nette\Database\Table\Selection',

            // https://github.com/nette/forms/compare/v3.0.7...v3.1.0-RC2
            'Nette\Forms\IControl' => 'Nette\Forms\Control',
            'Nette\Forms\IFormRenderer' => 'Nette\Forms\FormRenderer',
            'Nette\Forms\ISubmitterControl' => 'Nette\Forms\SubmitterControl',

            // https://github.com/nette/mail/compare/v3.0.1...v3.1.5
            'Nette\Mail\IMailer' => 'Nette\Mail\Mailer',

            // https://github.com/nette/security/compare/v3.0.5...v3.1.2
            'Nette\Security\IAuthorizator' => 'Nette\Security\Authorizator',
            'Nette\Security\Identity' => 'Nette\Security\SimpleIdentity',
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

    $services->set(RenameMethodRector::class)->call('configure', [[
        RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
            // https://github.com/nette/caching/commit/60281abf366c4ab76e9436dc1bfe2e402db18b67
            new MethodCallRename('Nette\Caching\Cache', 'start', 'capture'),
            // https://github.com/nette/forms/commit/faaaf8b8fd3408a274a9de7ca3f342091010ad5d
            new MethodCallRename('Nette\Forms\Container', 'addImage', 'addImageButton'),
            // https://github.com/nette/utils/commit/d0427c1811462dbb6c503143eabe5478b26685f7
            new MethodCallRename('Nette\Utils\Arrays', 'searchKey', 'getKeyOffset'),
            new MethodCallRename('Nette\Configurator', 'addParameters', 'addStaticParameters'),
        ]),
    ]]);

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => ValueObjectInliner::inline([
                // https://github.com/nette/utils/commit/8a4b795acd00f3f6754c28a73a7e776b60350c34
                new RenameStaticMethod('Nette\Utils\Callback', 'closure', 'Closure', 'fromCallable'),
            ]),
        ]]);

    $services->set(DimFetchAssignToMethodCallRector::class)
        ->call('configure', [[
            DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => ValueObjectInliner::inline([
                new DimFetchAssignToMethodCall(
                    'Nette\Application\Routers\RouteList',
                    'Nette\Application\Routers\Route',
                    'addRoute'
                ),
            ]),
        ]]);

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'sendTemplate',
                    0,
                    new UnionType([new ObjectType('Nette\Application\UI\Template'), new NullType()])
                ),
            ]),
        ]]);

    $services->set(ContextGetByTypeToConstructorInjectionRector::class);

    $services->set(ChangePackageVersionComposerRector::class)
        ->call('configure', [[
            ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                // meta package
                new PackageAndVersion('nette/nette', '^3.1'),
                // https://github.com/nette/nette/blob/v3.0.0/composer.json vs https://github.com/nette/nette/blob/v3.1.0/composer.json
                new PackageAndVersion('nette/application', '^3.1'),
                new PackageAndVersion('nette/bootstrap', '^3.1'),
                new PackageAndVersion('nette/caching', '^3.1'),
                new PackageAndVersion('nette/database', '^3.1'),
                new PackageAndVersion('nette/di', '^3.0'),
                new PackageAndVersion('nette/finder', '^2.5'),
                new PackageAndVersion('nette/forms', '^3.1'),
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

                // contributte
                new PackageAndVersion('contributte/console', '^0.9'),
                new PackageAndVersion('contributte/event-dispatcher', '^0.8'),
                new PackageAndVersion('contributte/event-dispatcher-extra', '^0.8'),

                // nettrine
                new PackageAndVersion('nettrine/annotations', '^0.7'),
                new PackageAndVersion('nettrine/cache', '^0.3'),
            ]),
        ]]);

    $services->set(RemovePackageComposerRector::class)
        ->call('configure', [[
            RemovePackageComposerRector::PACKAGE_NAMES => ['nette/component-model', 'nette/neon'],
        ]]);
};
