<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::SCALAR_TYPES - 1);
    $rectorConfig->rule(DowngradeSessionStartArrayOptionsRector::class);
};
