<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Param;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class MatchParamTypeExpectedNameResolver
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(StaticTypeMapper $staticTypeMapper, PropertyNaming $propertyNaming)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyNaming = $propertyNaming;
    }
    public function resolve(Param $param) : ?string
    {
        // nothing to verify
        if ($param->type === null) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        // include nullable too
        // skip date time + date time interface, as should be kept
        if ($this->isDateTimeType($staticType)) {
            return null;
        }
        $expectedName = $this->propertyNaming->getExpectedNameFromType($staticType);
        if (!$expectedName instanceof ExpectedName) {
            return null;
        }
        return $expectedName->getName();
    }
    private function isDateTimeType(Type $type) : bool
    {
        if ($type->isSuperTypeOf(new ObjectType('DateTimeInterface'))->yes()) {
            return \true;
        }
        return $type->isSuperTypeOf(new ObjectType('DateTime'))->yes();
    }
}
