<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
# see: https://laravel.com/docs/5.4/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(StringToClassConstantRector::class, [new StringToClassConstant('kernel.handled', 'RectorPrefix20220607\\Illuminate\\Foundation\\Http\\Events\\RequestHandled', 'class'), new StringToClassConstant('locale.changed', 'RectorPrefix20220607\\Illuminate\\Foundation\\Events\\LocaleUpdated', 'class'), new StringToClassConstant('illuminate.log', 'RectorPrefix20220607\\Illuminate\\Log\\Events\\MessageLogged', 'class')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Illuminate\\Console\\AppNamespaceDetectorTrait' => 'RectorPrefix20220607\\Illuminate\\Console\\DetectsApplicationNamespace', 'RectorPrefix20220607\\Illuminate\\Http\\Exception\\HttpResponseException' => 'RectorPrefix20220607\\Illuminate\\Http\\Exceptions\\HttpResponseException', 'RectorPrefix20220607\\Illuminate\\Http\\Exception\\PostTooLargeException' => 'RectorPrefix20220607\\Illuminate\\Http\\Exceptions\\PostTooLargeException', 'RectorPrefix20220607\\Illuminate\\Foundation\\Http\\Middleware\\VerifyPostSize' => 'RectorPrefix20220607\\Illuminate\\Foundation\\Http\\Middleware\\ValidatePostSize', 'RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\Session\\SessionInterface' => 'RectorPrefix20220607\\Illuminate\\Contracts\\Session\\Session']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Support\\Collection', 'every', 'nth'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany', 'setJoin', 'performJoin'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany', 'getRelatedIds', 'allRelatedIds'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Routing\\Router', 'middleware', 'aliasMiddleware'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Routing\\Route', 'getPath', 'uri'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Routing\\Route', 'getUri', 'uri'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Routing\\Route', 'getMethods', 'methods'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Routing\\Route', 'getParameter', 'parameter'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Session\\Session', 'set', 'put'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Session\\Session', 'getToken', 'token'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Support\\Facades\\Request', 'setSession', 'setLaravelSession'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Http\\Request', 'setSession', 'setLaravelSession'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Routing\\UrlGenerator', 'forceSchema', 'forceScheme'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Validation\\Validator', 'addError', 'addFailure'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Validation\\Validator', 'doReplacements', 'makeReplacements'),
        # https://github.com/laravel/framework/commit/f23ac640fa403ca8d4131c36367b53e123b6b852
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'seeInDatabase', 'assertDatabaseHas'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'missingFromDatabase', 'assertDatabaseMissing'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'dontSeeInDatabase', 'assertDatabaseMissing'),
        new MethodCallRename('RectorPrefix20220607\\Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'notSeeInDatabase', 'assertDatabaseMissing'),
    ]);
};
