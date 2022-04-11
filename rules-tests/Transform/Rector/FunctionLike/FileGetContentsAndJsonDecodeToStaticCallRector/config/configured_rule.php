<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

use Rector\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector;
use Rector\Transform\ValueObject\StaticCallRecipe;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(
        FileGetContentsAndJsonDecodeToStaticCallRector::class,
        [new StaticCallRecipe('FileLoader', 'loadJson')]
    );
};
