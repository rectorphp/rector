<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector;
use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\Rector\FuncCall\FuncCallToNewRector;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
/**
 * @see https://www.freecodecamp.org/news/moving-away-from-magic-or-why-i-dont-want-to-use-laravel-anymore-2ce098c979bd/
 * @see https://tomasvotruba.com/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/
 * @see https://laravel.com/docs/5.7/facades#facades-vs-dependency-injection
 */
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector::class)->configure([new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\App', '*', 'Illuminate\\Foundation\\Application', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Artisan', '*', 'Illuminate\\Contracts\\Console\\Kernel', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Auth', '*', 'Illuminate\\Auth\\AuthManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Blade', '*', 'Illuminate\\View\\Compilers\\BladeCompiler', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Broadcast', '*', 'Illuminate\\Contracts\\Broadcasting\\Factory', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Bus', '*', 'Illuminate\\Contracts\\Bus\\Dispatcher', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Cache', '*', 'Illuminate\\Cache\\CacheManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Config', '*', 'Illuminate\\Config\\Repository', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Cookie', '*', 'Illuminate\\Cookie\\CookieJar', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Crypt', '*', 'Illuminate\\Encryption\\Encrypter', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\DB', '*', 'Illuminate\\Database\\DatabaseManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Event', '*', 'Illuminate\\Events\\Dispatcher', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\File', '*', 'Illuminate\\Filesystem\\Filesystem', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Gate', '*', 'Illuminate\\Contracts\\Auth\\Access\\Gate', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Hash', '*', 'Illuminate\\Contracts\\Hashing\\Hasher', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Lang', '*', 'Illuminate\\Translation\\Translator', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Log', '*', 'Illuminate\\Log\\LogManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Mail', '*', 'Illuminate\\Mail\\Mailer', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Notification', '*', 'Illuminate\\Notifications\\ChannelManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Password', '*', 'Illuminate\\Auth\\Passwords\\PasswordBrokerManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Queue', '*', 'Illuminate\\Queue\\QueueManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Redirect', '*', 'Illuminate\\Routing\\Redirector', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Redis', '*', 'Illuminate\\Redis\\RedisManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Request', '*', 'Illuminate\\Http\\Request', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Response', '*', 'Illuminate\\Contracts\\Routing\\ResponseFactory', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Route', '*', 'Illuminate\\Routing\\Router', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Schema', '*', 'Illuminate\\Database\\Schema\\Builder', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Session', '*', 'Illuminate\\Session\\SessionManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Storage', '*', 'Illuminate\\Filesystem\\FilesystemManager', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\URL', '*', 'Illuminate\\Routing\\UrlGenerator', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\Validator', '*', 'Illuminate\\Validation\\Factory', '*'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Illuminate\\Support\\Facades\\View', '*', 'Illuminate\\View\\Factory', '*')]);
    $services->set(\Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector::class);
    // @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
    // @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
    $services->set(\Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector::class)->configure([
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('auth', 'Illuminate\\Contracts\\Auth\\Guard'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('policy', 'Illuminate\\Contracts\\Auth\\Access\\Gate', 'getPolicyFor'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('cookie', 'Illuminate\\Contracts\\Cookie\\Factory', 'make'),
        // router
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('put', 'Illuminate\\Routing\\Router', 'put'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('get', 'Illuminate\\Routing\\Router', 'get'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('post', 'Illuminate\\Routing\\Router', 'post'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('patch', 'Illuminate\\Routing\\Router', 'patch'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('delete', 'Illuminate\\Routing\\Router', 'delete'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('resource', 'Illuminate\\Routing\\Router', 'resource'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('response', 'Illuminate\\Contracts\\Routing\\ResponseFactory', 'make'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('info', 'Illuminate\\Log\\Writer', 'info'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('view', 'Illuminate\\Contracts\\View\\Factory', 'make'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('bcrypt', 'Illuminate\\Hashing\\BcryptHasher', 'make'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('redirect', 'Illuminate\\Routing\\Redirector', 'back'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('broadcast', 'Illuminate\\Contracts\\Broadcasting\\Factory', 'event'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('event', 'Illuminate\\Events\\Dispatcher', 'dispatch'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('dispatch', 'Illuminate\\Events\\Dispatcher', 'dispatch'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('route', 'Illuminate\\Routing\\UrlGenerator', 'route'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('asset', 'Illuminate\\Routing\\UrlGenerator', 'asset'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('url', 'Illuminate\\Contracts\\Routing\\UrlGenerator', 'to'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('action', 'Illuminate\\Routing\\UrlGenerator', 'action'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('trans', 'Illuminate\\Translation\\Translator', 'trans'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('trans_choice', 'Illuminate\\Translation\\Translator', 'transChoice'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('logger', 'Illuminate\\Log\\Writer', 'debug'),
        new \Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall('back', 'Illuminate\\Routing\\Redirector', 'back', 'back'),
        new \Rector\Transform\ValueObject\ArrayFuncCallToMethodCall('config', 'Illuminate\\Contracts\\Config\\Repository', 'set', 'get'),
        new \Rector\Transform\ValueObject\ArrayFuncCallToMethodCall('session', 'Illuminate\\Session\\SessionManager', 'put', 'get'),
    ]);
    $services->set(\Rector\Transform\Rector\FuncCall\FuncCallToNewRector::class)->configure(['collect' => 'Illuminate\\Support\\Collection']);
    $services->set(\Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector::class);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['App' => 'Illuminate\\Support\\Facades\\App', 'Artisan' => 'Illuminate\\Support\\Facades\\Artisan', 'Auth' => 'Illuminate\\Support\\Facades\\Auth', 'Blade' => 'Illuminate\\Support\\Facades\\Blade', 'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast', 'Bus' => 'Illuminate\\Support\\Facades\\Bus', 'Cache' => 'Illuminate\\Support\\Facades\\Cache', 'Config' => 'Illuminate\\Support\\Facades\\Config', 'Cookie' => 'Illuminate\\Support\\Facades\\Cookie', 'Crypt' => 'Illuminate\\Support\\Facades\\Crypt', 'DB' => 'Illuminate\\Support\\Facades\\DB', 'Date' => 'Illuminate\\Support\\Facades\\Date', 'Event' => 'Illuminate\\Support\\Facades\\Event', 'Facade' => 'Illuminate\\Support\\Facades\\Facade', 'File' => 'Illuminate\\Support\\Facades\\File', 'Gate' => 'Illuminate\\Support\\Facades\\Gate', 'Hash' => 'Illuminate\\Support\\Facades\\Hash', 'Http' => 'Illuminate\\Support\\Facades\\Http', 'Lang' => 'Illuminate\\Support\\Facades\\Lang', 'Log' => 'Illuminate\\Support\\Facades\\Log', 'Mail' => 'Illuminate\\Support\\Facades\\Mail', 'Notification' => 'Illuminate\\Support\\Facades\\Notification', 'Password' => 'Illuminate\\Support\\Facades\\Password', 'Queue' => 'Illuminate\\Support\\Facades\\Queue', 'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter', 'Redirect' => 'Illuminate\\Support\\Facades\\Redirect', 'Redis' => 'Illuminate\\Support\\Facades\\Redis', 'Request' => 'Illuminate\\Http\\Request', 'Response' => 'Illuminate\\Support\\Facades\\Response', 'Route' => 'Illuminate\\Support\\Facades\\Route', 'Schema' => 'Illuminate\\Support\\Facades\\Schema', 'Session' => 'Illuminate\\Support\\Facades\\Session', 'Storage' => 'Illuminate\\Support\\Facades\\Storage', 'URL' => 'Illuminate\\Support\\Facades\\URL', 'Validator' => 'Illuminate\\Support\\Facades\\Validator', 'View' => 'Illuminate\\Support\\Facades\\View']);
};
