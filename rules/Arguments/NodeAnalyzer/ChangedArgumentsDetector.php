<?php

declare (strict_types=1);
namespace Rector\Arguments\NodeAnalyzer;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ChangedArgumentsDetector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator)
    {
        $this->valueResolver = $valueResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
    }
    /**
     * @param mixed $value
     */
    public function isDefaultValueChanged(\PhpParser\Node\Param $param, $value) : bool
    {
        if ($param->default === null) {
            return \false;
        }
        return !$this->valueResolver->isValue($param->default, $value);
    }
    public function isTypeChanged(\PhpParser\Node\Param $param, ?\PHPStan\Type\Type $newType) : bool
    {
        if ($param->type === null) {
            return \false;
        }
        if ($newType === null) {
            return \true;
        }
        $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        return !$this->typeComparator->areTypesEqual($currentParamType, $newType);
    }
}
