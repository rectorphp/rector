<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(StaticCallToMethodCallRector::class)
        ->configure([
            new StaticCallToMethodCall(
                'Nette\Utils\FileSystem',
                'write',
                'Symplify\SmartFileSystem\SmartFileSystem',
                'dumpFile'
            ),
            new StaticCallToMethodCall(
                'Illuminate\Support\Facades\Response',
                '*',
                'Illuminate\Contracts\Routing\ResponseFactory',
                '*'
            ),
        ]);
};
