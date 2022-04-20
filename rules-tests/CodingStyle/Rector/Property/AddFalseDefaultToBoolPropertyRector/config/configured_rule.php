<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(AddFalseDefaultToBoolPropertyRector::class);
};
