<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(FuncCallToConstFetchRector::class)
        ->configure([
            'php_sapi_name' => 'PHP_SAPI',
            'pi' => 'M_PI',
        ]);
};
