<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeDirnameLevelsRector::class);
};
