<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use RectorPrefix20220606\Rector\Arguments\ValueObject\ArgumentAdder;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\Visibility;
use RectorPrefix20220606\Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector;
use RectorPrefix20220606\Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector;
use RectorPrefix20220606\Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector;
use RectorPrefix20220606\Rector\Laravel\Rector\New_\AddGuardToLoginEventRector;
use RectorPrefix20220606\Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use RectorPrefix20220606\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use RectorPrefix20220606\Rector\Removing\ValueObject\ArgumentRemover;
use RectorPrefix20220606\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use RectorPrefix20220606\Rector\Visibility\ValueObject\ChangeMethodVisibility;
# see: https://laravel.com/docs/5.7/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Illuminate\\Routing\\Router', 'addRoute', Visibility::PUBLIC), new ChangeMethodVisibility('Illuminate\\Contracts\\Auth\\Access\\Gate', 'raw', Visibility::PUBLIC)]);
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Auth\\Middleware\\Authenticate', 'authenticate', 0, 'request'), new ArgumentAdder('Illuminate\\Foundation\\Auth\\ResetsPasswords', 'sendResetResponse', 0, 'request', null, new ObjectType('Illuminate\\Http\\Illuminate\\Http')), new ArgumentAdder('Illuminate\\Foundation\\Auth\\SendsPasswordResetEmails', 'sendResetLinkResponse', 0, 'request', null, new ObjectType('Illuminate\\Http\\Illuminate\\Http')), new ArgumentAdder('Illuminate\\Database\\ConnectionInterface', 'select', 2, 'useReadPdo', \true), new ArgumentAdder('Illuminate\\Database\\ConnectionInterface', 'selectOne', 2, 'useReadPdo', \true)]);
    $rectorConfig->rule(Redirect301ToPermanentRedirectRector::class);
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Illuminate\\Foundation\\Application', 'register', 1, ['name' => 'options'])]);
    $rectorConfig->rule(AddParentBootToModelClassMethodRector::class);
    $rectorConfig->rule(ChangeQueryWhereDateValueWithCarbonRector::class);
    $rectorConfig->rule(AddMockConsoleOutputFalseToConsoleTestsRector::class);
    $rectorConfig->rule(AddGuardToLoginEventRector::class);
};
