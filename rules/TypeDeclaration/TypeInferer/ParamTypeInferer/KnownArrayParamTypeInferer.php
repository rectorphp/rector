<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
final class KnownArrayParamTypeInferer implements ParamTypeInfererInterface
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferParam(Param $param) : Type
    {
        $class = $this->betterNodeFinder->findParentType($param, Class_::class);
        if (!$class instanceof Class_) {
            return new MixedType();
        }
        $className = (string) $this->nodeNameResolver->getName($class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return new MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $paramName = $this->nodeNameResolver->getName($param);
        // @todo create map later
        if ($paramName === 'configs' && $classReflection->isSubclassOf('Symfony\\Component\\DependencyInjection\\Extension\\Extension')) {
            return new ArrayType(new MixedType(), new StringType());
        }
        return new MixedType();
    }
}
