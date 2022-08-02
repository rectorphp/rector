<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector;
use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\Rector\FuncCall\FuncCallToNewRector;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
/**
 * @see https://www.freecodecamp.org/news/moving-away-from-magic-or-why-i-dont-want-to-use-laravel-anymore-2ce098c979bd/
 * @see https://tomasvotruba.com/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/
 * @see https://laravel.com/docs/5.7/facades#facades-vs-dependency-injection
 */
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');
    $rectorConfig->ruleWithConfiguration(StaticCallToMethodCallRector::class, [new StaticCallToMethodCall('Illuminate\\Support\\Facades\\App', '*', 'Illuminate\\Foundation\\Application', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Artisan', '*', 'Illuminate\\Contracts\\Console\\Kernel', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Auth', '*', 'Illuminate\\Auth\\AuthManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Blade', '*', 'Illuminate\\View\\Compilers\\BladeCompiler', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Broadcast', '*', 'Illuminate\\Contracts\\Broadcasting\\Factory', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Bus', '*', 'Illuminate\\Contracts\\Bus\\Dispatcher', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Cache', '*', 'Illuminate\\Cache\\CacheManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Config', '*', 'Illuminate\\Config\\Repository', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Cookie', '*', 'Illuminate\\Cookie\\CookieJar', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Crypt', '*', 'Illuminate\\Encryption\\Encrypter', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\DB', '*', 'Illuminate\\Database\\DatabaseManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Event', '*', 'Illuminate\\Events\\Dispatcher', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\File', '*', 'Illuminate\\Filesystem\\Filesystem', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Gate', '*', 'Illuminate\\Contracts\\Auth\\Access\\Gate', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Hash', '*', 'Illuminate\\Contracts\\Hashing\\Hasher', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Lang', '*', 'Illuminate\\Translation\\Translator', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Log', '*', 'Illuminate\\Log\\LogManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Mail', '*', 'Illuminate\\Mail\\Mailer', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Notification', '*', 'Illuminate\\Notifications\\ChannelManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Password', '*', 'Illuminate\\Auth\\Passwords\\PasswordBrokerManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Queue', '*', 'Illuminate\\Queue\\QueueManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Redirect', '*', 'Illuminate\\Routing\\Redirector', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Redis', '*', 'Illuminate\\Redis\\RedisManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Request', '*', 'Illuminate\\Http\\Request', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Response', '*', 'Illuminate\\Contracts\\Routing\\ResponseFactory', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Route', '*', 'Illuminate\\Routing\\Router', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Schema', '*', 'Illuminate\\Database\\Schema\\Builder', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Session', '*', 'Illuminate\\Session\\SessionManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Storage', '*', 'Illuminate\\Filesystem\\FilesystemManager', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\URL', '*', 'Illuminate\\Routing\\UrlGenerator', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\Validator', '*', 'Illuminate\\Validation\\Factory', '*'), new StaticCallToMethodCall('Illuminate\\Support\\Facades\\View', '*', 'Illuminate\\View\\Factory', '*')]);
    $rectorConfig->rule(RequestStaticValidateToInjectRector::class);
    // @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
    // @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
    $rectorConfig->ruleWithConfiguration(ArgumentFuncCallToMethodCallRector::class, [
        new ArgumentFuncCallToMethodCall('auth', 'Illuminate\\Contracts\\Auth\\Guard'),
        new ArgumentFuncCallToMethodCall('policy', 'Illuminate\\Contracts\\Auth\\Access\\Gate', 'getPolicyFor'),
        new ArgumentFuncCallToMethodCall('cookie', 'Illuminate\\Contracts\\Cookie\\Factory', 'make'),
        // router
        new ArgumentFuncCallToMethodCall('put', 'Illuminate\\Routing\\Router', 'put'),
        new ArgumentFuncCallToMethodCall('get', 'Illuminate\\Routing\\Router', 'get'),
        new ArgumentFuncCallToMethodCall('post', 'Illuminate\\Routing\\Router', 'post'),
        new ArgumentFuncCallToMethodCall('patch', 'Illuminate\\Routing\\Router', 'patch'),
        new ArgumentFuncCallToMethodCall('delete', 'Illuminate\\Routing\\Router', 'delete'),
        new ArgumentFuncCallToMethodCall('resource', 'Illuminate\\Routing\\Router', 'resource'),
        new ArgumentFuncCallToMethodCall('response', 'Illuminate\\Contracts\\Routing\\ResponseFactory', 'make'),
        new ArgumentFuncCallToMethodCall('info', 'Illuminate\\Log\\Writer', 'info'),
        new ArgumentFuncCallToMethodCall('view', 'Illuminate\\Contracts\\View\\Factory', 'make'),
        new ArgumentFuncCallToMethodCall('bcrypt', 'Illuminate\\Hashing\\BcryptHasher', 'make'),
        new ArgumentFuncCallToMethodCall('redirect', 'Illuminate\\Routing\\Redirector', 'to'),
        new ArgumentFuncCallToMethodCall('broadcast', 'Illuminate\\Contracts\\Broadcasting\\Factory', 'event'),
        new ArgumentFuncCallToMethodCall('event', 'Illuminate\\Events\\Dispatcher', 'dispatch'),
        new ArgumentFuncCallToMethodCall('dispatch', 'Illuminate\\Events\\Dispatcher', 'dispatch'),
        new ArgumentFuncCallToMethodCall('route', 'Illuminate\\Routing\\UrlGenerator', 'route'),
        new ArgumentFuncCallToMethodCall('asset', 'Illuminate\\Routing\\UrlGenerator', 'asset'),
        new ArgumentFuncCallToMethodCall('url', 'Illuminate\\Contracts\\Routing\\UrlGenerator', 'to'),
        new ArgumentFuncCallToMethodCall('action', 'Illuminate\\Routing\\UrlGenerator', 'action'),
        new ArgumentFuncCallToMethodCall('trans', 'Illuminate\\Translation\\Translator', 'trans'),
        new ArgumentFuncCallToMethodCall('trans_choice', 'Illuminate\\Translation\\Translator', 'transChoice'),
        new ArgumentFuncCallToMethodCall('logger', 'Illuminate\\Log\\Writer', 'debug'),
        new ArgumentFuncCallToMethodCall('back', 'Illuminate\\Routing\\Redirector', 'back', 'back'),
        new ArrayFuncCallToMethodCall('config', 'Illuminate\\Contracts\\Config\\Repository', 'set', 'get'),
        new ArrayFuncCallToMethodCall('session', 'Illuminate\\Session\\SessionManager', 'put', 'get'),
    ]);
    $rectorConfig->ruleWithConfiguration(FuncCallToNewRector::class, ['collect' => 'Illuminate\\Support\\Collection']);
    $rectorConfig->rule(HelperFuncCallToFacadeClassRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['App' => 'Illuminate\\Support\\Facades\\App', 'Artisan' => 'Illuminate\\Support\\Facades\\Artisan', 'Auth' => 'Illuminate\\Support\\Facades\\Auth', 'Blade' => 'Illuminate\\Support\\Facades\\Blade', 'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast', 'Bus' => 'Illuminate\\Support\\Facades\\Bus', 'Cache' => 'Illuminate\\Support\\Facades\\Cache', 'Config' => 'Illuminate\\Support\\Facades\\Config', 'Cookie' => 'Illuminate\\Support\\Facades\\Cookie', 'Crypt' => 'Illuminate\\Support\\Facades\\Crypt', 'DB' => 'Illuminate\\Support\\Facades\\DB', 'Date' => 'Illuminate\\Support\\Facades\\Date', 'Event' => 'Illuminate\\Support\\Facades\\Event', 'Facade' => 'Illuminate\\Support\\Facades\\Facade', 'File' => 'Illuminate\\Support\\Facades\\File', 'Gate' => 'Illuminate\\Support\\Facades\\Gate', 'Hash' => 'Illuminate\\Support\\Facades\\Hash', 'Http' => 'Illuminate\\Support\\Facades\\Http', 'Lang' => 'Illuminate\\Support\\Facades\\Lang', 'Log' => 'Illuminate\\Support\\Facades\\Log', 'Mail' => 'Illuminate\\Support\\Facades\\Mail', 'Notification' => 'Illuminate\\Support\\Facades\\Notification', 'Password' => 'Illuminate\\Support\\Facades\\Password', 'Queue' => 'Illuminate\\Support\\Facades\\Queue', 'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter', 'Redirect' => 'Illuminate\\Support\\Facades\\Redirect', 'Redis' => 'Illuminate\\Support\\Facades\\Redis', 'Request' => 'Illuminate\\Http\\Request', 'Response' => 'Illuminate\\Support\\Facades\\Response', 'Route' => 'Illuminate\\Support\\Facades\\Route', 'Schema' => 'Illuminate\\Support\\Facades\\Schema', 'Session' => 'Illuminate\\Support\\Facades\\Session', 'Storage' => 'Illuminate\\Support\\Facades\\Storage', 'URL' => 'Illuminate\\Support\\Facades\\URL', 'Validator' => 'Illuminate\\Support\\Facades\\Validator', 'View' => 'Illuminate\\Support\\Facades\\View']);
};
