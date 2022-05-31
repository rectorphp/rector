<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
# see: https://laravel.com/docs/5.4/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\String_\StringToClassConstantRector::class, [new \Rector\Transform\ValueObject\StringToClassConstant('kernel.handled', 'Illuminate\\Foundation\\Http\\Events\\RequestHandled', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('locale.changed', 'Illuminate\\Foundation\\Events\\LocaleUpdated', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('illuminate.log', 'Illuminate\\Log\\Events\\MessageLogged', 'class')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Illuminate\\Console\\AppNamespaceDetectorTrait' => 'Illuminate\\Console\\DetectsApplicationNamespace', 'Illuminate\\Http\\Exception\\HttpResponseException' => 'Illuminate\\Http\\Exceptions\\HttpResponseException', 'Illuminate\\Http\\Exception\\PostTooLargeException' => 'Illuminate\\Http\\Exceptions\\PostTooLargeException', 'Illuminate\\Foundation\\Http\\Middleware\\VerifyPostSize' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidatePostSize', 'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface' => 'Illuminate\\Contracts\\Session\\Session']);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Support\\Collection', 'every', 'nth'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany', 'setJoin', 'performJoin'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany', 'getRelatedIds', 'allRelatedIds'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Routing\\Router', 'middleware', 'aliasMiddleware'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Routing\\Route', 'getPath', 'uri'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Routing\\Route', 'getUri', 'uri'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Routing\\Route', 'getMethods', 'methods'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Routing\\Route', 'getParameter', 'parameter'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Session\\Session', 'set', 'put'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Session\\Session', 'getToken', 'token'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Support\\Facades\\Request', 'setSession', 'setLaravelSession'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Http\\Request', 'setSession', 'setLaravelSession'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Routing\\UrlGenerator', 'forceSchema', 'forceScheme'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Validation\\Validator', 'addError', 'addFailure'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Validation\\Validator', 'doReplacements', 'makeReplacements'),
        # https://github.com/laravel/framework/commit/f23ac640fa403ca8d4131c36367b53e123b6b852
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'seeInDatabase', 'assertDatabaseHas'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'missingFromDatabase', 'assertDatabaseMissing'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'dontSeeInDatabase', 'assertDatabaseMissing'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithDatabase', 'notSeeInDatabase', 'assertDatabaseMissing'),
    ]);
};
