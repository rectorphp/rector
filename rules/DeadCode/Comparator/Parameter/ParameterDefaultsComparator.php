<?php

declare (strict_types=1);
namespace Rector\DeadCode\Comparator\Parameter;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PHPStan\Reflection\ParameterReflection;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver;
final class ParameterDefaultsComparator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver
     */
    private $defaultParameterValueResolver;
    public function __construct(NodeComparator $nodeComparator, DefaultParameterValueResolver $defaultParameterValueResolver)
    {
        $this->nodeComparator = $nodeComparator;
        $this->defaultParameterValueResolver = $defaultParameterValueResolver;
    }
    public function areDefaultValuesDifferent(ParameterReflection $parameterReflection, Param $param) : bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default === null) {
            return \false;
        }
        if ($this->isMutuallyExclusiveNull($parameterReflection, $param)) {
            return \true;
        }
        /** @var Expr $paramDefault */
        $paramDefault = $param->default;
        $defaultValueExpr = $this->defaultParameterValueResolver->resolveFromParameterReflection($parameterReflection);
        return !$this->nodeComparator->areNodesEqual($paramDefault, $defaultValueExpr);
    }
    private function isMutuallyExclusiveNull(ParameterReflection $parameterReflection, Param $param) : bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default !== null) {
            return \true;
        }
        if ($parameterReflection->getDefaultValue() === null) {
            return \false;
        }
        return $param->default === null;
    }
}
