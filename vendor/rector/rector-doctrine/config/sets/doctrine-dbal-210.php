<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Doctrine\\DBAL\\Types\\Type' => 'Doctrine\\DBAL\\Types\\Types']);
};
