<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    // forms 3.1
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class, [new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Nette\\Application\\UI\\Form', 'values', 'getValues')]);
    // some attributes were added in nette 3.0, but only in one of latest patch versions; it's is safer to add them in 3.1
    $rectorConfig->sets([\Rector\Nette\Set\NetteSetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector::class, [
        // see https://github.com/nette/caching/commit/5ffe263752af5ccf3866a28305e7b2669ab4da82
        new \Rector\Transform\ValueObject\CallableInMethodCallToVariable('Nette\\Caching\\Cache', 'save', 1),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        'Nette\\Bridges\\ApplicationLatte\\Template' => 'Nette\\Bridges\\ApplicationLatte\\DefaultTemplate',
        // https://github.com/nette/application/compare/v3.0.7...v3.1.0
        'Nette\\Application\\IRouter' => 'Nette\\Routing\\Router',
        'Nette\\Application\\IResponse' => 'Nette\\Application\\Response',
        'Nette\\Application\\UI\\IRenderable' => 'Nette\\Application\\UI\\Renderable',
        'Nette\\Application\\UI\\ISignalReceiver' => 'Nette\\Application\\UI\\SignalReceiver',
        'Nette\\Application\\UI\\IStatePersistent' => 'Nette\\Application\\UI\\StatePersistent',
        'Nette\\Application\\UI\\ITemplate' => 'Nette\\Application\\UI\\Template',
        'Nette\\Application\\UI\\ITemplateFactory' => 'Nette\\Application\\UI\\TemplateFactory',
        'Nette\\Bridges\\ApplicationLatte\\ILatteFactory' => 'Nette\\Bridges\\ApplicationLatte\\LatteFactory',
        // https://github.com/nette/bootstrap/compare/v3.0.2...v3.1.0
        'Nette\\Configurator' => 'Nette\\Bootstrap\\Configurator',
        // https://github.com/nette/caching/compare/v3.0.2...v3.1.0
        'Nette\\Caching\\IBulkReader' => 'Nette\\Caching\\BulkReader',
        'Nette\\Caching\\IStorage' => 'Nette\\Caching\\Storage',
        'Nette\\Caching\\Storages\\IJournal' => 'Nette\\Caching\\Storages\\Journal',
        // https://github.com/nette/database/compare/v3.0.7...v3.1.1
        'Nette\\Database\\ISupplementalDriver' => 'Nette\\Database\\Driver',
        'Nette\\Database\\IConventions' => 'Nette\\Database\\Conventions',
        'Nette\\Database\\Context' => 'Nette\\Database\\Explorer',
        'Nette\\Database\\IRow' => 'Nette\\Database\\Row',
        'Nette\\Database\\IRowContainer' => 'Nette\\Database\\ResultSet',
        'Nette\\Database\\Table\\IRow' => 'Nette\\Database\\Table\\ActiveRow',
        'Nette\\Database\\Table\\IRowContainer' => 'Nette\\Database\\Table\\Selection',
        // https://github.com/nette/forms/compare/v3.0.7...v3.1.0-RC2
        'Nette\\Forms\\IControl' => 'Nette\\Forms\\Control',
        'Nette\\Forms\\IFormRenderer' => 'Nette\\Forms\\FormRenderer',
        'Nette\\Forms\\ISubmitterControl' => 'Nette\\Forms\\SubmitterControl',
        // https://github.com/nette/mail/compare/v3.0.1...v3.1.5
        'Nette\\Mail\\IMailer' => 'Nette\\Mail\\Mailer',
        // https://github.com/nette/security/compare/v3.0.5...v3.1.2
        'Nette\\Security\\IAuthorizator' => 'Nette\\Security\\Authorizator',
        'Nette\\Security\\Identity' => 'Nette\\Security\\SimpleIdentity',
        'Nette\\Security\\IResource' => 'Nette\\Security\\Resource',
        'Nette\\Security\\IRole' => 'Nette\\Security\\Role',
        // https://github.com/nette/utils/compare/v3.1.4...v3.2.1
        'Nette\\Utils\\IHtmlString' => 'Nette\\HtmlStringable',
        'Nette\\Localization\\ITranslator' => 'Nette\\Localization\\Translator',
        // https://github.com/nette/latte/compare/v2.5.5...v2.9.2
        'Latte\\ILoader' => 'Latte\\Loader',
        'Latte\\IMacro' => 'Latte\\Macro',
        'Latte\\Runtime\\IHtmlString' => 'Latte\\Runtime\\HtmlStringable',
        'Latte\\Runtime\\ISnippetBridge' => 'Latte\\Runtime\\SnippetBridge',
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        // https://github.com/nette/caching/commit/60281abf366c4ab76e9436dc1bfe2e402db18b67
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\Caching\\Cache', 'start', 'capture'),
        // https://github.com/nette/forms/commit/faaaf8b8fd3408a274a9de7ca3f342091010ad5d
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\Forms\\Container', 'addImage', 'addImageButton'),
        // https://github.com/nette/utils/commit/d0427c1811462dbb6c503143eabe5478b26685f7
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\Utils\\Arrays', 'searchKey', 'getKeyOffset'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\Configurator', 'addParameters', 'addStaticParameters'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class, [
        // https://github.com/nette/utils/commit/8a4b795acd00f3f6754c28a73a7e776b60350c34
        new \Rector\Renaming\ValueObject\RenameStaticMethod('Nette\\Utils\\Callback', 'closure', 'Closure', 'fromCallable'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector::class, [new \Rector\Transform\ValueObject\DimFetchAssignToMethodCall('Nette\\Application\\Routers\\RouteList', 'Nette\\Application\\Routers\\Route', 'addRoute')]);
    $nullableTemplateType = new \PHPStan\Type\UnionType([new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Template'), new \PHPStan\Type\NullType()]);
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class, [new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Nette\\Application\\UI\\Presenter', 'sendTemplate', 0, $nullableTemplateType)]);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Composer\Rector\ChangePackageVersionComposerRector::class, [
        // meta package
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/nette', '^3.1'),
        // https://github.com/nette/nette/blob/v3.0.0/composer.json vs https://github.com/nette/nette/blob/v3.1.0/composer.json
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/application', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/bootstrap', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/caching', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/database', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/di', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/finder', '^2.5'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/forms', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/http', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/mail', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/php-generator', '^3.5'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/robot-loader', '^3.3'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/safe-stream', '^2.4'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/security', '^3.1'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/tokenizer', '^3.0'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nette/utils', '^3.2'),
        new \Rector\Composer\ValueObject\PackageAndVersion('latte/latte', '^2.9'),
        new \Rector\Composer\ValueObject\PackageAndVersion('tracy/tracy', '^2.8'),
        // contributte
        new \Rector\Composer\ValueObject\PackageAndVersion('contributte/console', '^0.9'),
        new \Rector\Composer\ValueObject\PackageAndVersion('contributte/event-dispatcher', '^0.8'),
        new \Rector\Composer\ValueObject\PackageAndVersion('contributte/event-dispatcher-extra', '^0.8'),
        // nettrine
        new \Rector\Composer\ValueObject\PackageAndVersion('nettrine/annotations', '^0.7'),
        new \Rector\Composer\ValueObject\PackageAndVersion('nettrine/cache', '^0.3'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Composer\Rector\RemovePackageComposerRector::class, ['nette/component-model', 'nette/neon']);
};
