<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# see: https://laravel.com/docs/5.2/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Illuminate\Auth\Access\UnauthorizedException' => 'Illuminate\Auth\Access\AuthorizationException',
                'Illuminate\Http\Exception\HttpResponseException' => 'Illuminate\Foundation\Validation\ValidationException',
                'Illuminate\Foundation\Composer' => 'Illuminate\Support\Composer',
            ],
        ]]);

    $services->set(StringToClassConstantRector::class)
        ->call('configure', [[
            StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => ValueObjectInliner::inline([
                new StringToClassConstant('artisan.start', 'Illuminate\Console\Events\ArtisanStarting', 'class'),
                new StringToClassConstant('auth.attempting', 'Illuminate\Auth\Events\Attempting', 'class'),
                new StringToClassConstant('auth.login', 'Illuminate\Auth\Events\Login', 'class'),
                new StringToClassConstant('auth.logout', 'Illuminate\Auth\Events\Logout', 'class'),
                new StringToClassConstant('cache.missed', 'Illuminate\Cache\Events\CacheMissed', 'class'),
                new StringToClassConstant('cache.hit', 'Illuminate\Cache\Events\CacheHit', 'class'),
                new StringToClassConstant('cache.write', 'Illuminate\Cache\Events\KeyWritten', 'class'),
                new StringToClassConstant('cache.delete', 'Illuminate\Cache\Events\KeyForgotten', 'class'),
                new StringToClassConstant('illuminate.query', 'Illuminate\Database\Events\QueryExecuted', 'class'),
                new StringToClassConstant(
                    'illuminate.queue.before',
                    'Illuminate\Queue\Events\JobProcessing',
                    'class'
                ),
                new StringToClassConstant(
                    'illuminate.queue.after',
                    'Illuminate\Queue\Events\JobProcessed',
                    'class'
                ),
                new StringToClassConstant('illuminate.queue.failed', 'Illuminate\Queue\Events\JobFailed', 'class'),
                new StringToClassConstant(
                    'illuminate.queue.stopping',
                    'Illuminate\Queue\Events\WorkerStopping',
                    'class'
                ),
                new StringToClassConstant('mailer.sending', 'Illuminate\Mail\Events\MessageSending', 'class'),
                new StringToClassConstant('router.matched', 'Illuminate\Routing\Events\RouteMatched', 'class'),
            ]),
        ]]);
};
