<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Enum;

use Rector\Doctrine\Enum\MappingClass;
use Rector\Doctrine\Enum\OdmMappingClass;
final class CollectionMapping
{
    /**
     * @var string[]
     */
    public const TO_MANY_CLASSES = [MappingClass::ONE_TO_MANY, MappingClass::MANY_TO_MANY, OdmMappingClass::REFERENCE_MANY];
    /**
     * @var string[]
     */
    public const TO_ONE_CLASSES = [MappingClass::MANY_TO_ONE, MappingClass::ONE_TO_ONE];
}
