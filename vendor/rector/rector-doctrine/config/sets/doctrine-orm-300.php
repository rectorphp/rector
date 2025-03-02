<?php

declare (strict_types=1);
namespace RectorPrefix202503;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Doctrine\\ORM\\ORMException' => 'Doctrine\\ORM\\Exception\\ORMException']);
};
