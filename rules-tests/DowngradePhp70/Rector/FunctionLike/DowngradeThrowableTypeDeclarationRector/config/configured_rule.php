<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeThrowableTypeDeclarationRector::class);
};
