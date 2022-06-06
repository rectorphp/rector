<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeFactory\InvokableController;

use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassConst;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\Symfony\ValueObject\InvokableController\ActiveClassElements;
final class ActiveClassElementsFilter
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return ClassConst[]
     */
    public function filterClassConsts(Class_ $class, ActiveClassElements $activeClassElements) : array
    {
        return \array_filter($class->getConstants(), function (ClassConst $classConst) use($activeClassElements) {
            /** @var string $constantName */
            $constantName = $this->nodeNameResolver->getName($classConst);
            return $activeClassElements->hasConstantName($constantName);
        });
    }
    /**
     * @return Property[]
     */
    public function filterProperties(Class_ $class, ActiveClassElements $activeClassElements) : array
    {
        return \array_filter($class->getProperties(), function (Property $property) use($activeClassElements) {
            // keep only property used in current action
            $propertyName = $this->nodeNameResolver->getName($property);
            return $activeClassElements->hasPropertyName($propertyName);
        });
    }
    /**
     * @return ClassMethod[]
     */
    public function filterClassMethod(Class_ $class, ActiveClassElements $activeClassElements) : array
    {
        return \array_filter($class->getMethods(), function (ClassMethod $classMethod) use($activeClassElements) {
            if ($classMethod->isPublic()) {
                return \false;
            }
            /** @var string $classMethodName */
            $classMethodName = $this->nodeNameResolver->getName($classMethod->name);
            return $activeClassElements->hasMethodName($classMethodName);
        });
    }
}
