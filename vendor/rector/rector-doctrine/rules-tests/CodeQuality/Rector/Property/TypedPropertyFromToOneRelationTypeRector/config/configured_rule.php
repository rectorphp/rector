<?php

declare (strict_types=1);
namespace RectorPrefix202307;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use Rector\Doctrine\Tests\ConfigList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(ConfigList::MAIN);
    $rectorConfig->rule(TypedPropertyFromToOneRelationTypeRector::class);
    $rectorConfig->phpVersion(PhpVersionFeature::UNION_TYPES);
};
