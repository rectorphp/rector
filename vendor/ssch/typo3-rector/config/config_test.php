<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/config.php');
    $rectorConfig->importNames();
    $rectorConfig->phpstanConfig(\Ssch\TYPO3Rector\Configuration\Typo3Option::PHPSTAN_FOR_RECTOR_PATH);
};
