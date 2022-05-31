<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Doctrine\\DBAL\\Types\\Type' => 'Doctrine\\DBAL\\Types\\Types']);
};
