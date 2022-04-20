<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MbStrrposEncodingArgumentPositionRector::class);
};
