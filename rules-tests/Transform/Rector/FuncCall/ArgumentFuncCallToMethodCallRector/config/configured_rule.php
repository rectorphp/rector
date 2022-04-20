<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(ArgumentFuncCallToMethodCallRector::class, [
            new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'make'),
            new ArgumentFuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'route'),
            new ArgumentFuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'back', 'back'),
            new ArgumentFuncCallToMethodCall('broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'event'),

            new ArrayFuncCallToMethodCall('config', 'Illuminate\Contracts\Config\Repository', 'set', 'get'),
            new ArrayFuncCallToMethodCall('session', 'Illuminate\Session\SessionManager', 'put', 'get'),
        ]);
};
