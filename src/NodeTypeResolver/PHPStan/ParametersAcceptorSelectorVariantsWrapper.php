<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\FunctionLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
final class ParametersAcceptorSelectorVariantsWrapper
{
    /**
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $reflection
     * @param \PhpParser\Node\Expr\CallLike|\PhpParser\Node\FunctionLike $node
     */
    public static function select($reflection, $node, Scope $scope) : ParametersAcceptor
    {
        $variants = $reflection->getVariants();
        if ($node instanceof FunctionLike) {
            return ParametersAcceptorSelector::combineAcceptors($variants);
        }
        if ($node->isFirstClassCallable()) {
            return ParametersAcceptorSelector::selectSingle($variants);
        }
        return \count($variants) > 1 ? ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $variants) : ParametersAcceptorSelector::selectSingle($variants);
    }
}
