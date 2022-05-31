<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PHPStan\Type\ObjectType;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector;
use Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector;
use Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector;
use Rector\Laravel\Rector\New_\AddGuardToLoginEventRector;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
# see: https://laravel.com/docs/5.7/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector::class, [new \Rector\Visibility\ValueObject\ChangeMethodVisibility('Illuminate\\Routing\\Router', 'addRoute', \Rector\Core\ValueObject\Visibility::PUBLIC), new \Rector\Visibility\ValueObject\ChangeMethodVisibility('Illuminate\\Contracts\\Auth\\Access\\Gate', 'raw', \Rector\Core\ValueObject\Visibility::PUBLIC)]);
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class, [new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Auth\\Middleware\\Authenticate', 'authenticate', 0, 'request'), new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Foundation\\Auth\\ResetsPasswords', 'sendResetResponse', 0, 'request', null, new \PHPStan\Type\ObjectType('Illuminate\\Http\\Illuminate\\Http')), new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Foundation\\Auth\\SendsPasswordResetEmails', 'sendResetLinkResponse', 0, 'request', null, new \PHPStan\Type\ObjectType('Illuminate\\Http\\Illuminate\\Http')), new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Database\\ConnectionInterface', 'select', 2, 'useReadPdo', \true), new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Database\\ConnectionInterface', 'selectOne', 2, 'useReadPdo', \true)]);
    $rectorConfig->rule(\Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector::class, [new \Rector\Removing\ValueObject\ArgumentRemover('Illuminate\\Foundation\\Application', 'register', 1, ['name' => 'options'])]);
    $rectorConfig->rule(\Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector::class);
    $rectorConfig->rule(\Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector::class);
    $rectorConfig->rule(\Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector::class);
    $rectorConfig->rule(\Rector\Laravel\Rector\New_\AddGuardToLoginEventRector::class);
};
