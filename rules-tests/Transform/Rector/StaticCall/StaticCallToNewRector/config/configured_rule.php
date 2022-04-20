<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\StaticCallToNew;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew(SomeJsonResponse::class, 'create')]);
};
