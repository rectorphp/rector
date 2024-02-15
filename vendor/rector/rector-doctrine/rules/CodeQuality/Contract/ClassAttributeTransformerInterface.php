<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Contract;

use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
interface ClassAttributeTransformerInterface
{
    /**
     * @return MappingClass::*
     */
    public function getClassName() : string;
    public function transform(EntityMapping $entityMapping, Class_ $class) : void;
}
