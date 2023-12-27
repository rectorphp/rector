<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Contract;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
interface PropertyAnnotationTransformerInterface
{
    public function transform(EntityMapping $entityMapping, PhpDocInfo $propertyPhpDocInfo, Property $property) : void;
}
