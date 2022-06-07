<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
# see: https://laravel.com/docs/5.2/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Illuminate\\Auth\\Access\\UnauthorizedException' => 'RectorPrefix20220607\\Illuminate\\Auth\\Access\\AuthorizationException', 'RectorPrefix20220607\\Illuminate\\Http\\Exception\\HttpResponseException' => 'RectorPrefix20220607\\Illuminate\\Foundation\\Validation\\ValidationException', 'RectorPrefix20220607\\Illuminate\\Foundation\\Composer' => 'RectorPrefix20220607\\Illuminate\\Support\\Composer']);
    $rectorConfig->ruleWithConfiguration(StringToClassConstantRector::class, [new StringToClassConstant('artisan.start', 'RectorPrefix20220607\\Illuminate\\Console\\Events\\ArtisanStarting', 'class'), new StringToClassConstant('auth.attempting', 'RectorPrefix20220607\\Illuminate\\Auth\\Events\\Attempting', 'class'), new StringToClassConstant('auth.login', 'RectorPrefix20220607\\Illuminate\\Auth\\Events\\Login', 'class'), new StringToClassConstant('auth.logout', 'RectorPrefix20220607\\Illuminate\\Auth\\Events\\Logout', 'class'), new StringToClassConstant('cache.missed', 'RectorPrefix20220607\\Illuminate\\Cache\\Events\\CacheMissed', 'class'), new StringToClassConstant('cache.hit', 'RectorPrefix20220607\\Illuminate\\Cache\\Events\\CacheHit', 'class'), new StringToClassConstant('cache.write', 'RectorPrefix20220607\\Illuminate\\Cache\\Events\\KeyWritten', 'class'), new StringToClassConstant('cache.delete', 'RectorPrefix20220607\\Illuminate\\Cache\\Events\\KeyForgotten', 'class'), new StringToClassConstant('illuminate.query', 'RectorPrefix20220607\\Illuminate\\Database\\Events\\QueryExecuted', 'class'), new StringToClassConstant('illuminate.queue.before', 'RectorPrefix20220607\\Illuminate\\Queue\\Events\\JobProcessing', 'class'), new StringToClassConstant('illuminate.queue.after', 'RectorPrefix20220607\\Illuminate\\Queue\\Events\\JobProcessed', 'class'), new StringToClassConstant('illuminate.queue.failed', 'RectorPrefix20220607\\Illuminate\\Queue\\Events\\JobFailed', 'class'), new StringToClassConstant('illuminate.queue.stopping', 'RectorPrefix20220607\\Illuminate\\Queue\\Events\\WorkerStopping', 'class'), new StringToClassConstant('mailer.sending', 'RectorPrefix20220607\\Illuminate\\Mail\\Events\\MessageSending', 'class'), new StringToClassConstant('router.matched', 'RectorPrefix20220607\\Illuminate\\Routing\\Events\\RouteMatched', 'class')]);
};
