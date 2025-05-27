<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\ValueObject\MethodName;
final class CollectionParamCallDetector
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $callLike
     */
    public function detect($callLike, int $position) : bool
    {
        if ($callLike instanceof StaticCall) {
            $callerType = $this->nodeTypeResolver->getType($callLike->class);
            $methodName = $this->nodeNameResolver->getName($callLike->name);
        } elseif ($callLike instanceof MethodCall) {
            // does setter method require a collection?
            $callerType = $this->nodeTypeResolver->getType($callLike->var);
            $methodName = $this->nodeNameResolver->getName($callLike->name);
        } else {
            $callerType = $this->nodeTypeResolver->getType($callLike->class);
            $methodName = MethodName::CONSTRUCT;
        }
        $callerType = TypeCombinator::removeNull($callerType);
        if (!$callerType instanceof ObjectType) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($callerType->getClassName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($callerType->getClassName());
        if ($methodName === null) {
            return \false;
        }
        $scope = ScopeFetcher::fetch($callLike);
        if (!$classReflection->hasMethod($methodName)) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getMethod($methodName, $scope);
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $activeParameterReflection = $extendedParametersAcceptor->getParameters()[$position] ?? null;
        if (!$activeParameterReflection instanceof PhpParameterReflection) {
            return \false;
        }
        $parameterType = $activeParameterReflection->getType();
        // to include nullables
        $parameterType = TypeCombinator::removeNull($parameterType);
        if (!$parameterType instanceof ObjectType) {
            return \false;
        }
        return $parameterType->isInstanceOf(DoctrineClass::COLLECTION)->yes();
    }
}
