<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(TypedPropertyFromToOneRelationTypeRector::class);
};
