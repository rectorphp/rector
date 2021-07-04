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
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver
     */
    private $defaultParameterValueResolver;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver $defaultParameterValueResolver)
    {
        $this->nodeComparator = $nodeComparator;
        $this->defaultParameterValueResolver = $defaultParameterValueResolver;
    }
    public function areDefaultValuesDifferent(\PHPStan\Reflection\ParameterReflection $parameterReflection, \PhpParser\Node\Param $param) : bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default === null) {
            return \false;
        }
        if ($this->isMutuallyExclusiveNull($parameterReflection, $param)) {
            return \true;
        }
        /** @var Expr $paramDefault */
        $paramDefault = $param->default;
        $firstParameterValue = $this->defaultParameterValueResolver->resolveFromParameterReflection($parameterReflection);
        return !$this->nodeComparator->areNodesEqual($paramDefault, $firstParameterValue);
    }
    private function isMutuallyExclusiveNull(\PHPStan\Reflection\ParameterReflection $parameterReflection, \PhpParser\Node\Param $param) : bool
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
