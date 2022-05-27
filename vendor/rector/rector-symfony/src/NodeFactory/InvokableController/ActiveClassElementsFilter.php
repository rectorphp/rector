<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\InvokableController;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\ValueObject\InvokableController\ActiveClassElements;
final class ActiveClassElementsFilter
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return ClassConst[]
     */
    public function filterClassConsts(\PhpParser\Node\Stmt\Class_ $class, \Rector\Symfony\ValueObject\InvokableController\ActiveClassElements $activeClassElements) : array
    {
        return \array_filter($class->getConstants(), function (\PhpParser\Node\Stmt\ClassConst $classConst) use($activeClassElements) {
            /** @var string $constantName */
            $constantName = $this->nodeNameResolver->getName($classConst);
            return $activeClassElements->hasConstantName($constantName);
        });
    }
    /**
     * @return Property[]
     */
    public function filterProperties(\PhpParser\Node\Stmt\Class_ $class, \Rector\Symfony\ValueObject\InvokableController\ActiveClassElements $activeClassElements) : array
    {
        return \array_filter($class->getProperties(), function (\PhpParser\Node\Stmt\Property $property) use($activeClassElements) {
            // keep only property used in current action
            $propertyName = $this->nodeNameResolver->getName($property);
            return $activeClassElements->hasPropertyName($propertyName);
        });
    }
    /**
     * @return ClassMethod[]
     */
    public function filterClassMethod(\PhpParser\Node\Stmt\Class_ $class, \Rector\Symfony\ValueObject\InvokableController\ActiveClassElements $activeClassElements) : array
    {
        return \array_filter($class->getMethods(), function (\PhpParser\Node\Stmt\ClassMethod $classMethod) use($activeClassElements) {
            if ($classMethod->isPublic()) {
                return \false;
            }
            /** @var string $classMethodName */
            $classMethodName = $this->nodeNameResolver->getName($classMethod->name);
            return $activeClassElements->hasMethodName($classMethodName);
        });
    }
}
