<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\PropertyItem;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PropertyTypeDefaultValueAnalyzer
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function doesConflictWithDefaultValue(PropertyItem $propertyItem, Type $propertyType) : bool
    {
        if (!$propertyItem->default instanceof Expr) {
            return \false;
        }
        // the defaults can be in conflict
        $defaultType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($propertyItem->default);
        if ($defaultType->isArray()->yes() && $propertyType->isArray()->yes()) {
            return \false;
        }
        // type is not matching, skip it
        return !$defaultType->isSuperTypeOf($propertyType)->yes();
    }
}
