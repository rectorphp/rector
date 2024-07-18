<?php

declare (strict_types=1);
namespace Rector\DeadCode\SideEffect;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeNameResolver\NodeNameResolver;
final class PureFunctionDetector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function detect(FuncCall $funcCall, Scope $scope) : bool
    {
        $funcCallName = $this->nodeNameResolver->getName($funcCall);
        if ($funcCallName === null) {
            return \false;
        }
        $name = new Name($funcCallName);
        $hasFunction = $this->reflectionProvider->hasFunction($name, $scope);
        if (!$hasFunction) {
            return \false;
        }
        $functionReflection = $this->reflectionProvider->getFunction($name, $scope);
        if (!$functionReflection instanceof NativeFunctionReflection) {
            return \false;
        }
        // yes() and maybe() may have side effect
        return $functionReflection->hasSideEffects()->no();
    }
}
