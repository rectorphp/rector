<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
final class ParametersAcceptorSelectorVariantsWrapper
{
    /**
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $reflection
     */
    public static function select($reflection, CallLike $callLike, Scope $scope) : ParametersAcceptor
    {
        $variants = $reflection->getVariants();
        if ($callLike->isFirstClassCallable()) {
            return ParametersAcceptorSelector::selectSingle($variants);
        }
        return \count($variants) > 1 ? ParametersAcceptorSelector::selectFromArgs($scope, $callLike->getArgs(), $variants) : ParametersAcceptorSelector::selectSingle($variants);
    }
}
