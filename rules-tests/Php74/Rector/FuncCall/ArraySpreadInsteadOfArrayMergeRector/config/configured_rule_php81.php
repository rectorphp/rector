<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->rule(ArraySpreadInsteadOfArrayMergeRector::class);
};