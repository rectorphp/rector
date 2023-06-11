<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFinder;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Reflection\ReflectionResolver;
final class DataProviderClassMethodFinder
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReflectionResolver $reflectionResolver, AstResolver $astResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
    }
    /**
     * @return ClassMethod[]
     */
    public function find(Class_ $class) : array
    {
        $parentAbstractClasses = $this->resolveParentAbstractClasses($class);
        $targetClasses = \array_merge([$class], $parentAbstractClasses);
        // foreach to find method names
        $dataProviderMethodNames = [];
        foreach ($targetClasses as $targetClass) {
            $dataProviderMethodNames = \array_merge($dataProviderMethodNames, $this->resolverDataProviderClassMethodNames($targetClass));
        }
        $dataProviderClassMethods = [];
        foreach ($dataProviderMethodNames as $dataProviderMethodName) {
            $dataProviderClassMethod = $class->getMethod($dataProviderMethodName);
            if (!$dataProviderClassMethod instanceof ClassMethod) {
                continue;
            }
            $dataProviderClassMethods[] = $dataProviderClassMethod;
        }
        return $dataProviderClassMethods;
    }
    /**
     * @api
     * @return string[]
     */
    public function findDataProviderNamesForClassMethod(ClassMethod $classMethod) : array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $dataProviderTagValueNodes = $phpDocInfo->getTagsByName('dataProvider');
        if ($dataProviderTagValueNodes === []) {
            return [];
        }
        $dataProviderMethodNames = [];
        foreach ($dataProviderTagValueNodes as $dataProviderTagValueNode) {
            if (!$dataProviderTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $dataProviderMethodNames[] = $this->resolveMethodName($dataProviderTagValueNode->value);
        }
        return $dataProviderMethodNames;
    }
    /**
     * @return string[]
     */
    private function resolverDataProviderClassMethodNames(Class_ $class) : array
    {
        $dataProviderMethodNames = [];
        foreach ($class->getMethods() as $classMethod) {
            $currentDataProviderMethodNames = $this->findDataProviderNamesForClassMethod($classMethod);
            $dataProviderMethodNames = \array_merge($dataProviderMethodNames, $currentDataProviderMethodNames);
        }
        return $dataProviderMethodNames;
    }
    private function resolveMethodName(GenericTagValueNode $genericTagValueNode) : string
    {
        $rawValue = $genericTagValueNode->value;
        return \trim($rawValue, '()');
    }
    /**
     * @return Class_[]
     */
    private function resolveParentAbstractClasses(Class_ $class) : array
    {
        // resolve from parent one?
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        $parentClasses = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            // is the top parent class? stop
            if ($parentClassReflection->getName() === 'PHPUnit\\Framework\\TestCase') {
                break;
            }
            /** @var Class_ $parentClass */
            $parentClass = $this->astResolver->resolveClassFromClassReflection($parentClassReflection);
            $parentClasses[] = $parentClass;
        }
        return $parentClasses;
    }
}
