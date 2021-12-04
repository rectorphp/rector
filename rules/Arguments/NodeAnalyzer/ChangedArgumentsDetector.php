<?php

declare(strict_types=1);

namespace Rector\Arguments\NodeAnalyzer;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ChangedArgumentsDetector
{
    public function __construct(
        private readonly ValueResolver $valueResolver,
        private readonly StaticTypeMapper $staticTypeMapper,
        private readonly TypeComparator $typeComparator
    ) {
    }

    /**
     * @param mixed $value
     */
    public function isDefaultValueChanged(Param $param, $value): bool
    {
        if ($param->default === null) {
            return false;
        }

        return ! $this->valueResolver->isValue($param->default, $value);
    }

    public function isTypeChanged(Param $param, ?Type $newType): bool
    {
        if ($param->type === null) {
            return false;
        }

        if ($newType === null) {
            return true;
        }

        $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);

        return ! $this->typeComparator->areTypesEqual($currentParamType, $newType);
    }
}
