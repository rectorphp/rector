<?php

declare(strict_types=1);
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Generic\ValueObject\ArgumentRemover;
use Rector\Generic\ValueObject\ChangeMethodVisibility;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# see: https://laravel.com/docs/5.7/upgrade
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector::class)->call('configure', [[\Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector::METHOD_VISIBILITIES => \Rector\SymfonyPhpConfig\inline_value_objects([new \Rector\Generic\ValueObject\ChangeMethodVisibility('Illuminate\Routing\Router', 'addRoute', 'public'), new \Rector\Generic\ValueObject\ChangeMethodVisibility('Illuminate\Contracts\Auth\Access\Gate', 'raw', 'public')])]]);
    $services->set(\Rector\Generic\Rector\ClassMethod\ArgumentAdderRector::class)->call('configure', [[\Rector\Generic\Rector\ClassMethod\ArgumentAdderRector::ADDED_ARGUMENTS => \Rector\SymfonyPhpConfig\inline_value_objects([new \Rector\Generic\ValueObject\ArgumentAdder('Illuminate\Auth\Middleware\Authenticate', 'authenticate', 0, 'request'), new \Rector\Generic\ValueObject\ArgumentAdder('Illuminate\Foundation\Auth\ResetsPasswords', 'sendResetResponse', 0, 'request', \null, 'Illuminate\Http\Illuminate\Http'), new \Rector\Generic\ValueObject\ArgumentAdder('Illuminate\Foundation\Auth\SendsPasswordResetEmails', 'sendResetLinkResponse', 0, 'request', \null, 'Illuminate\Http\Illuminate\Http')])]]);
    $services->set(\Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector::class);
    $services->set(\Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector::class)->call('configure', [[\Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector::REMOVED_ARGUMENTS => \Rector\SymfonyPhpConfig\inline_value_objects([new \Rector\Generic\ValueObject\ArgumentRemover('Illuminate\Foundation\Application', 'register', 1, ['name' => 'options'])])]]);
    $services->set(\Rector\Laravel\Rector\Class_\AddParentBootToModelClassMethodRector::class);
};
