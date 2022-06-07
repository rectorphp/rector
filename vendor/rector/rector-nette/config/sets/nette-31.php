<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\Rector\RemovePackageComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Config\RectorConfig;
use Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector;
use Rector\Nette\Set\NetteSetList;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    // forms 3.1
    $rectorConfig->ruleWithConfiguration(PropertyFetchToMethodCallRector::class, [new PropertyFetchToMethodCall('RectorPrefix20220607\\Nette\\Application\\UI\\Form', 'values', 'getValues')]);
    // some attributes were added in nette 3.0, but only in one of latest patch versions; it's is safer to add them in 3.1
    $rectorConfig->sets([NetteSetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->ruleWithConfiguration(CallableInMethodCallToVariableRector::class, [
        // see https://github.com/nette/caching/commit/5ffe263752af5ccf3866a28305e7b2669ab4da82
        new CallableInMethodCallToVariable('RectorPrefix20220607\\Nette\\Caching\\Cache', 'save', 1),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'RectorPrefix20220607\\Nette\\Bridges\\ApplicationLatte\\Template' => 'RectorPrefix20220607\\Nette\\Bridges\\ApplicationLatte\\DefaultTemplate',
        // https://github.com/nette/application/compare/v3.0.7...v3.1.0
        'RectorPrefix20220607\\Nette\\Application\\IRouter' => 'RectorPrefix20220607\\Nette\\Routing\\Router',
        'RectorPrefix20220607\\Nette\\Application\\IResponse' => 'RectorPrefix20220607\\Nette\\Application\\Response',
        'RectorPrefix20220607\\Nette\\Application\\UI\\IRenderable' => 'RectorPrefix20220607\\Nette\\Application\\UI\\Renderable',
        'RectorPrefix20220607\\Nette\\Application\\UI\\ISignalReceiver' => 'RectorPrefix20220607\\Nette\\Application\\UI\\SignalReceiver',
        'RectorPrefix20220607\\Nette\\Application\\UI\\IStatePersistent' => 'RectorPrefix20220607\\Nette\\Application\\UI\\StatePersistent',
        'RectorPrefix20220607\\Nette\\Application\\UI\\ITemplate' => 'RectorPrefix20220607\\Nette\\Application\\UI\\Template',
        'RectorPrefix20220607\\Nette\\Application\\UI\\ITemplateFactory' => 'RectorPrefix20220607\\Nette\\Application\\UI\\TemplateFactory',
        'RectorPrefix20220607\\Nette\\Bridges\\ApplicationLatte\\ILatteFactory' => 'RectorPrefix20220607\\Nette\\Bridges\\ApplicationLatte\\LatteFactory',
        // https://github.com/nette/bootstrap/compare/v3.0.2...v3.1.0
        'RectorPrefix20220607\\Nette\\Configurator' => 'RectorPrefix20220607\\Nette\\Bootstrap\\Configurator',
        // https://github.com/nette/caching/compare/v3.0.2...v3.1.0
        'RectorPrefix20220607\\Nette\\Caching\\IBulkReader' => 'RectorPrefix20220607\\Nette\\Caching\\BulkReader',
        'RectorPrefix20220607\\Nette\\Caching\\IStorage' => 'RectorPrefix20220607\\Nette\\Caching\\Storage',
        'RectorPrefix20220607\\Nette\\Caching\\Storages\\IJournal' => 'RectorPrefix20220607\\Nette\\Caching\\Storages\\Journal',
        // https://github.com/nette/database/compare/v3.0.7...v3.1.1
        'RectorPrefix20220607\\Nette\\Database\\ISupplementalDriver' => 'RectorPrefix20220607\\Nette\\Database\\Driver',
        'RectorPrefix20220607\\Nette\\Database\\IConventions' => 'RectorPrefix20220607\\Nette\\Database\\Conventions',
        'RectorPrefix20220607\\Nette\\Database\\Context' => 'RectorPrefix20220607\\Nette\\Database\\Explorer',
        'RectorPrefix20220607\\Nette\\Database\\IRow' => 'RectorPrefix20220607\\Nette\\Database\\Row',
        'RectorPrefix20220607\\Nette\\Database\\IRowContainer' => 'RectorPrefix20220607\\Nette\\Database\\ResultSet',
        'RectorPrefix20220607\\Nette\\Database\\Table\\IRow' => 'RectorPrefix20220607\\Nette\\Database\\Table\\ActiveRow',
        'RectorPrefix20220607\\Nette\\Database\\Table\\IRowContainer' => 'RectorPrefix20220607\\Nette\\Database\\Table\\Selection',
        // https://github.com/nette/forms/compare/v3.0.7...v3.1.0-RC2
        'RectorPrefix20220607\\Nette\\Forms\\IControl' => 'RectorPrefix20220607\\Nette\\Forms\\Control',
        'RectorPrefix20220607\\Nette\\Forms\\IFormRenderer' => 'RectorPrefix20220607\\Nette\\Forms\\FormRenderer',
        'RectorPrefix20220607\\Nette\\Forms\\ISubmitterControl' => 'RectorPrefix20220607\\Nette\\Forms\\SubmitterControl',
        // https://github.com/nette/mail/compare/v3.0.1...v3.1.5
        'RectorPrefix20220607\\Nette\\Mail\\IMailer' => 'RectorPrefix20220607\\Nette\\Mail\\Mailer',
        // https://github.com/nette/security/compare/v3.0.5...v3.1.2
        'RectorPrefix20220607\\Nette\\Security\\IAuthorizator' => 'RectorPrefix20220607\\Nette\\Security\\Authorizator',
        'RectorPrefix20220607\\Nette\\Security\\Identity' => 'RectorPrefix20220607\\Nette\\Security\\SimpleIdentity',
        'RectorPrefix20220607\\Nette\\Security\\IResource' => 'RectorPrefix20220607\\Nette\\Security\\Resource',
        'RectorPrefix20220607\\Nette\\Security\\IRole' => 'RectorPrefix20220607\\Nette\\Security\\Role',
        // https://github.com/nette/utils/compare/v3.1.4...v3.2.1
        'RectorPrefix20220607\\Nette\\Utils\\IHtmlString' => 'RectorPrefix20220607\\Nette\\HtmlStringable',
        'RectorPrefix20220607\\Nette\\Localization\\ITranslator' => 'RectorPrefix20220607\\Nette\\Localization\\Translator',
        // https://github.com/nette/latte/compare/v2.5.5...v2.9.2
        'RectorPrefix20220607\\Latte\\ILoader' => 'RectorPrefix20220607\\Latte\\Loader',
        'RectorPrefix20220607\\Latte\\IMacro' => 'RectorPrefix20220607\\Latte\\Macro',
        'RectorPrefix20220607\\Latte\\Runtime\\IHtmlString' => 'RectorPrefix20220607\\Latte\\Runtime\\HtmlStringable',
        'RectorPrefix20220607\\Latte\\Runtime\\ISnippetBridge' => 'RectorPrefix20220607\\Latte\\Runtime\\SnippetBridge',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/nette/caching/commit/60281abf366c4ab76e9436dc1bfe2e402db18b67
        new MethodCallRename('RectorPrefix20220607\\Nette\\Caching\\Cache', 'start', 'capture'),
        // https://github.com/nette/forms/commit/faaaf8b8fd3408a274a9de7ca3f342091010ad5d
        new MethodCallRename('RectorPrefix20220607\\Nette\\Forms\\Container', 'addImage', 'addImageButton'),
        // https://github.com/nette/utils/commit/d0427c1811462dbb6c503143eabe5478b26685f7
        new MethodCallRename('RectorPrefix20220607\\Nette\\Utils\\Arrays', 'searchKey', 'getKeyOffset'),
        new MethodCallRename('RectorPrefix20220607\\Nette\\Configurator', 'addParameters', 'addStaticParameters'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameStaticMethodRector::class, [
        // https://github.com/nette/utils/commit/8a4b795acd00f3f6754c28a73a7e776b60350c34
        new RenameStaticMethod('RectorPrefix20220607\\Nette\\Utils\\Callback', 'closure', 'Closure', 'fromCallable'),
    ]);
    $rectorConfig->ruleWithConfiguration(DimFetchAssignToMethodCallRector::class, [new DimFetchAssignToMethodCall('RectorPrefix20220607\\Nette\\Application\\Routers\\RouteList', 'RectorPrefix20220607\\Nette\\Application\\Routers\\Route', 'addRoute')]);
    $nullableTemplateType = new UnionType([new ObjectType('RectorPrefix20220607\\Nette\\Application\\UI\\Template'), new NullType()]);
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Application\\UI\\Presenter', 'sendTemplate', 0, $nullableTemplateType)]);
    $rectorConfig->rule(ContextGetByTypeToConstructorInjectionRector::class);
    $rectorConfig->ruleWithConfiguration(ChangePackageVersionComposerRector::class, [
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
    ]);
    $rectorConfig->ruleWithConfiguration(RemovePackageComposerRector::class, ['nette/component-model', 'nette/neon']);
};
