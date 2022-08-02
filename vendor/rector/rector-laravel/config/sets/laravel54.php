<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
# see: https://laravel.com/docs/5.4/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(StringToClassConstantRector::class, [new StringToClassConstant('kernel.handled', 'Illuminate\\Foundation\\Http\\Events\\RequestHandled', 'class'), new StringToClassConstant('locale.changed', 'Illuminate\\Foundation\\Events\\LocaleUpdated', 'class'), new StringToClassConstant('illuminate.log', 'Illuminate\\Log\\Events\\MessageLogged', 'class')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Illuminate\\Console\\AppNamespaceDetectorTrait' => 'Illuminate\\Console\\DetectsApplicationNamespace', 'Illuminate\\Http\\Exception\\HttpResponseException' => 'Illuminate\\Http\\Exceptions\\HttpResponseException', 'Illuminate\\Http\\Exception\\PostTooLargeException' => 'Illuminate\\Http\\Exceptions\\PostTooLargeException', 'Illuminate\\Foundation\\Http\\Middleware\\VerifyPostSize' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidatePostSize', 'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface' => 'Illuminate\\Contracts\\Session\\Session']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('Illuminate\\Support\\Collection', 'every', 'nth'),
        new MethodCallRename('Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany', 'setJoin', 'performJoin'),
        new MethodCallRename('Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany', 'getRelatedIds', 'allRelatedIds'),
        new MethodCallRename('Illuminate\\Routing\\Router', 'middleware', 'aliasMiddleware'),
        new MethodCallRename('Illuminate\\Routing\\Route', 'getPath', 'uri'),
        new MethodCallRename('Illuminate\\Routing\\Route', 'getUri', 'uri'),
        new MethodCallRename('Illuminate\\Routing\\Route', 'getMethods', 'methods'),
        new MethodCallRename('Illuminate\\Routing\\Route', 'getParameter', 'parameter'),
        new MethodCallRename('Illuminate\\Contracts\\Session\\Session', 'set', 'put'),
        new MethodCallRename('Illuminate\\Contracts\\Session\\Session', 'getToken', 'token'),
        new MethodCallRename('Illuminate\\Support\\Facades\\Request', 'setSession', 'setLaravelSession'),
        new MethodCallRename('Illuminate\\Http\\Request', 'setSession', 'setLaravelSession'),
        new MethodCallRename('Illuminate\\Routing\\UrlGenerator', 'forceSchema', 'forceScheme'),
        new MethodCallRename('Illuminate\\Validation\\Validator', 'addError', 'addFailure'),
        new MethodCallRename('Illuminate\\Validation\\Validator', 'doReplacements', 'makeReplacements'),
        # https://github.com/laravel/framework/commit/f23ac640fa403ca8d4131c36367b53e123b6b852
        new MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'seeInDatabase', 'assertDatabaseHas'),
        new MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'missingFromDatabase', 'assertDatabaseMissing'),
        new MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'dontSeeInDatabase', 'assertDatabaseMissing'),
        new MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'notSeeInDatabase', 'assertDatabaseMissing'),
    ]);
};
