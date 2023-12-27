<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Contract;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
interface ClassAnnotationTransformerInterface
{
    public function transform(EntityMapping $entityMapping, PhpDocInfo $propertyPhpDocInfo) : void;
}
