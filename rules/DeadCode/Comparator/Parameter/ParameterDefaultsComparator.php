<?php

declare(strict_types=1);

namespace Rector\DeadCode\Comparator\Parameter;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PHPStan\Reflection\ParameterReflection;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver;

final class ParameterDefaultsComparator
{
    public function __construct(
        private NodeComparator $nodeComparator,
        private DefaultParameterValueResolver $defaultParameterValueResolver
    ) {
    }

    public function areDefaultValuesDifferent(ParameterReflection $parameterReflection, Param $param): bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default === null) {
            return false;
        }

        if ($this->isMutuallyExclusiveNull($parameterReflection, $param)) {
            return true;
        }

        /** @var Expr $paramDefault */
        $paramDefault = $param->default;

        $firstParameterValue = $this->defaultParameterValueResolver->resolveFromParameterReflection(
            $parameterReflection
        );

        return ! $this->nodeComparator->areNodesEqual($paramDefault, $firstParameterValue);
    }

    private function isMutuallyExclusiveNull(ParameterReflection $parameterReflection, Param $param): bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default !== null) {
            return true;
        }

        if ($parameterReflection->getDefaultValue() === null) {
            return false;
        }

        return $param->default === null;
    }
}
