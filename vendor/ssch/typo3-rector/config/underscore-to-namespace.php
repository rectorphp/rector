<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/config.php');
    $rectorConfig->ruleWithConfiguration(\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class, [__DIR__ . '/../Migrations/Code/ClassAliasMap.php']);
};
