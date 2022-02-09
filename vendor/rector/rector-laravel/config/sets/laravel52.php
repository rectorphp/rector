<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# see: https://laravel.com/docs/5.2/upgrade
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Illuminate\\Auth\\Access\\UnauthorizedException' => 'Illuminate\\Auth\\Access\\AuthorizationException', 'Illuminate\\Http\\Exception\\HttpResponseException' => 'Illuminate\\Foundation\\Validation\\ValidationException', 'Illuminate\\Foundation\\Composer' => 'Illuminate\\Support\\Composer']);
    $services->set(\Rector\Transform\Rector\String_\StringToClassConstantRector::class)->configure([new \Rector\Transform\ValueObject\StringToClassConstant('artisan.start', 'Illuminate\\Console\\Events\\ArtisanStarting', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('auth.attempting', 'Illuminate\\Auth\\Events\\Attempting', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('auth.login', 'Illuminate\\Auth\\Events\\Login', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('auth.logout', 'Illuminate\\Auth\\Events\\Logout', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('cache.missed', 'Illuminate\\Cache\\Events\\CacheMissed', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('cache.hit', 'Illuminate\\Cache\\Events\\CacheHit', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('cache.write', 'Illuminate\\Cache\\Events\\KeyWritten', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('cache.delete', 'Illuminate\\Cache\\Events\\KeyForgotten', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('illuminate.query', 'Illuminate\\Database\\Events\\QueryExecuted', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('illuminate.queue.before', 'Illuminate\\Queue\\Events\\JobProcessing', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('illuminate.queue.after', 'Illuminate\\Queue\\Events\\JobProcessed', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('illuminate.queue.failed', 'Illuminate\\Queue\\Events\\JobFailed', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('illuminate.queue.stopping', 'Illuminate\\Queue\\Events\\WorkerStopping', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('mailer.sending', 'Illuminate\\Mail\\Events\\MessageSending', 'class'), new \Rector\Transform\ValueObject\StringToClassConstant('router.matched', 'Illuminate\\Routing\\Events\\RouteMatched', 'class')]);
};
