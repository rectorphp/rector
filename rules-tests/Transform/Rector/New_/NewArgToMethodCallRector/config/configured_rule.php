<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;
use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(
            NewArgToMethodCallRector::class,
            [new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv')]
        );
};
