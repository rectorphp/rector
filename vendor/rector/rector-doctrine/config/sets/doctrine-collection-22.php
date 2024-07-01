<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Doctrine\\Common\\Collections\\Criteria', 'ASC', 'Doctrine\\Common\\Collections\\Order', 'Ascending'), new RenameClassAndConstFetch('Doctrine\\Common\\Collections\\Criteria', 'DESC', 'Doctrine\\Common\\Collections\\Order', 'Descending')]);
};
