<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/config.php');
    $rectorConfig->ruleWithConfiguration(RenameClassMapAliasRector::class, [__DIR__ . '/../Migrations/Code/ClassAliasMap.php']);
};
