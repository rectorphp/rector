<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
# see https://laravel.com/docs/6.x/upgrade
# https://github.com/laravel/docs/pull/5531/files
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    # https://github.com/laravel/framework/commit/67a38ba0fa2acfbd1f4af4bf7d462bb4419cc091
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename(
        'Illuminate\\Auth\\Access\\Gate',
        # https://github.com/laravel/framework/commit/69de466ddc25966a0f6551f48acab1afa7bb9424
        'access',
        'inspect'
    ), new \Rector\Renaming\ValueObject\MethodCallRename(
        'Illuminate\\Support\\Facades\\Lang',
        # https://github.com/laravel/framework/commit/efbe23c4116f86846ad6edc0d95cd56f4175a446
        'trans',
        'get'
    ), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Support\\Facades\\Lang', 'transChoice', 'choice'), new \Rector\Renaming\ValueObject\MethodCallRename(
        'Illuminate\\Translation\\Translator',
        # https://github.com/laravel/framework/commit/697b898a1c89881c91af83ecc4493fa681e2aa38
        'getFromJson',
        'get'
    )]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class, [
        // https://github.com/laravel/framework/commit/55785d3514a8149d4858acef40c56a31b6b2ccd1
        new \Rector\Renaming\ValueObject\RenameStaticMethod('Illuminate\\Support\\Facades\\Input', 'get', 'Illuminate\\Support\\Facades\\Request', 'input'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Illuminate\\Support\\Facades\\Input' => 'Illuminate\\Support\\Facades\\Request']);
    $rectorConfig->ruleWithConfiguration(\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector::class, [new \Rector\Visibility\ValueObject\ChangeMethodVisibility('Illuminate\\Foundation\\Http\\FormRequest', 'validationData', \Rector\Core\ValueObject\Visibility::PUBLIC)]);
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class, [
        // https://github.com/laravel/framework/commit/6c1e014943a508afb2c10869c3175f7783a004e1
        new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Database\\Capsule\\Manager', 'table', 1, 'as', null),
        new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Database\\Connection', 'table', 1, 'as', null),
        new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Database\\ConnectionInterface', 'table', 1, 'as', null),
        new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Database\\Query\\Builder', 'from', 1, 'as', null),
    ]);
};
