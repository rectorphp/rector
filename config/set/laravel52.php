<?php

declare(strict_types=1);

use Rector\Generic\Rector\String_\StringToClassConstantRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
            StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => [
                'artisan.start' => ['Illuminate\Console\Events\ArtisanStarting', 'class'],
                'auth.attempting' => ['Illuminate\Auth\Events\Attempting', 'class'],
                'auth.login' => ['Illuminate\Auth\Events\Login', 'class'],
                'auth.logout' => ['Illuminate\Auth\Events\Logout', 'class'],
                'cache.missed' => ['Illuminate\Cache\Events\CacheMissed', 'class'],
                'cache.hit' => ['Illuminate\Cache\Events\CacheHit', 'class'],
                'cache.write' => ['Illuminate\Cache\Events\KeyWritten', 'class'],
                'cache.delete' => ['Illuminate\Cache\Events\KeyForgotten', 'class'],
                'illuminate.query' => ['Illuminate\Database\Events\QueryExecuted', 'class'],
                'illuminate.queue.before' => ['Illuminate\Queue\Events\JobProcessing', 'class'],
                'illuminate.queue.after' => ['Illuminate\Queue\Events\JobProcessed', 'class'],
                'illuminate.queue.failed' => ['Illuminate\Queue\Events\JobFailed', 'class'],
                'illuminate.queue.stopping' => ['Illuminate\Queue\Events\WorkerStopping', 'class'],
                'mailer.sending' => ['Illuminate\Mail\Events\MessageSending', 'class'],
                'router.matched' => ['Illuminate\Routing\Events\RouteMatched', 'class'],
            ],
        ]]);
};
