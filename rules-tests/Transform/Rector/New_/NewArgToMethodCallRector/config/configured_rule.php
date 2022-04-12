<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;
use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(NewArgToMethodCallRector::class)
        ->configure([new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv')]);
};
