<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Doctrine\\DBAL\\Types\\Type' => 'Doctrine\\DBAL\\Types\\Types']);
};
