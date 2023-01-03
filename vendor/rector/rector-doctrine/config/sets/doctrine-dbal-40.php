<?php

declare (strict_types=1);
namespace RectorPrefix202301;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Doctrine\\DBAL\\Connection', 'PARAM_STR_ARRAY', 'Doctrine\\DBAL\\ArrayParameterType', 'STRING')]);
    // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connection_schemamanager-and-connectiongetschemamanager
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Doctrine\\DBAL\\Connection', 'getSchemaManager', 'createSchemaManager')]);
};
