<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Tests\Issues\AliasedImportDouble\Rector\ClassMethod\AddAliasImportRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(AddAliasImportRector::class);
};
