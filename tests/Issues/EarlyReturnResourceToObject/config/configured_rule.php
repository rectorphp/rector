<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(Php8ResourceReturnToObjectRector::class);
    $rectorConfig->sets([SetList::EARLY_RETURN]);
};
