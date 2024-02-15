<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Contract;

use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
interface PropertyAttributeTransformerInterface
{
    public function getClassName() : string;
    public function transform(EntityMapping $entityMapping, Property $property) : void;
}
