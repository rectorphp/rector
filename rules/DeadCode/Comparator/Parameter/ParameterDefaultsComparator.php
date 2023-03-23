<?php

declare (strict_types=1);
namespace Rector\DeadCode\Comparator\Parameter;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;
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
        if (!$parameterReflection->getDefaultValue() instanceof Type && !$param->default instanceof Expr) {
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
        if (!$parameterReflection->getDefaultValue() instanceof Type && $param->default instanceof Expr) {
            return \true;
        }
        if (!$parameterReflection->getDefaultValue() instanceof Type) {
            return \false;
        }
        return !$param->default instanceof Expr;
    }
}
