<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(SetList::EARLY_RETURN);

    $services = $rectorConfig->services();
    $services->set(Php8ResourceReturnToObjectRector::class);
};
