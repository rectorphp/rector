<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PropertyTypeDefaultValueAnalyzer
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function doesConflictWithDefaultValue(PropertyProperty $propertyProperty, Type $propertyType) : bool
    {
        if (!$propertyProperty->default instanceof Expr) {
            return \false;
        }
        // the defaults can be in conflict
        $defaultType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($propertyProperty->default);
        // type is not matching, skip it
        return !$defaultType->isSuperTypeOf($propertyType)->yes();
    }
}
