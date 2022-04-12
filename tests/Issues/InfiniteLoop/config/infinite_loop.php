<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\Config\RectorConfig;
use Rector\Core\Tests\Issues\InfiniteLoop\Rector\MethodCall\InfinityLoopRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(InfinityLoopRector::class);
    $services->set(SimplifyDeMorganBinaryRector::class);
};
