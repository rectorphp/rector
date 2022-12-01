<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $oldClass = 'Doctrine\\DBAL\\Types\\Type';
    $newClass = 'Doctrine\\DBAL\\Types\\Types';
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch($oldClass, 'TARRAY', $newClass, 'ARRAY'), new RenameClassAndConstFetch($oldClass, 'SIMPLE_ARRAY', $newClass, 'SIMPLE_ARRAY'), new RenameClassAndConstFetch($oldClass, 'JSON_ARRAY', $newClass, 'JSON_ARRAY'), new RenameClassAndConstFetch($oldClass, 'JSON', $newClass, 'JSON'), new RenameClassAndConstFetch($oldClass, 'BIGINT', $newClass, 'BIGINT'), new RenameClassAndConstFetch($oldClass, 'BOOLEAN', $newClass, 'BOOLEAN'), new RenameClassAndConstFetch($oldClass, 'DATETIME', $newClass, 'DATETIME_MUTABLE'), new RenameClassAndConstFetch($oldClass, 'DATETIME_IMMUTABLE', $newClass, 'DATETIME_IMMUTABLE'), new RenameClassAndConstFetch($oldClass, 'DATETIMETZ', $newClass, 'DATETIMETZ_MUTABLE'), new RenameClassAndConstFetch($oldClass, 'DATETIMETZ_IMMUTABLE', $newClass, 'DATETIMETZ_IMMUTABLE'), new RenameClassAndConstFetch($oldClass, 'DATE', $newClass, 'DATE_MUTABLE'), new RenameClassAndConstFetch($oldClass, 'DATE_IMMUTABLE', $newClass, 'DATE_IMMUTABLE'), new RenameClassAndConstFetch($oldClass, 'TIME', $newClass, 'TIME_MUTABLE'), new RenameClassAndConstFetch($oldClass, 'TIME_IMMUTABLE', $newClass, 'TIME_IMMUTABLE'), new RenameClassAndConstFetch($oldClass, 'DECIMAL', $newClass, 'DECIMAL'), new RenameClassAndConstFetch($oldClass, 'INTEGER', $newClass, 'INTEGER'), new RenameClassAndConstFetch($oldClass, 'OBJECT', $newClass, 'OBJECT'), new RenameClassAndConstFetch($oldClass, 'SMALLINT', $newClass, 'SMALLINT'), new RenameClassAndConstFetch($oldClass, 'STRING', $newClass, 'STRING'), new RenameClassAndConstFetch($oldClass, 'TEXT', $newClass, 'TEXT'), new RenameClassAndConstFetch($oldClass, 'BINARY', $newClass, 'BINARY'), new RenameClassAndConstFetch($oldClass, 'BLOB', $newClass, 'BLOB'), new RenameClassAndConstFetch($oldClass, 'FLOAT', $newClass, 'FLOAT'), new RenameClassAndConstFetch($oldClass, 'GUID', $newClass, 'GUID'), new RenameClassAndConstFetch($oldClass, 'DATEINTERVAL', $newClass, 'DATEINTERVAL')]);
};
