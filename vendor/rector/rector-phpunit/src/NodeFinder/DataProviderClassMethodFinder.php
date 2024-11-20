<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFinder;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\AstResolver;
use Rector\Reflection\ReflectionResolver;
final class DataProviderClassMethodFinder
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReflectionResolver $reflectionResolver, AstResolver $astResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->nodeNameResolver = $nodeNameResolver;
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
     * @return string[]
     */
    public function findDataProviderNamesForClassMethod(ClassMethod $classMethod) : array
    {
        $dataProviderAttributes = $this->findAttributesByClass($classMethod, 'PHPUnit\\Framework\\Attributes\\DataProvider');
        if ($dataProviderAttributes !== []) {
            return $this->resolveAttributeMethodNames($dataProviderAttributes);
        }
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
     * @param class-string $attributeClass
     * @return Attribute[]
     */
    public function findAttributesByClass(ClassMethod $classMethod, string $attributeClass) : array
    {
        $foundAttributes = [];
        /** @var AttributeGroup $attrGroup */
        foreach ($classMethod->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$attribute->name instanceof FullyQualified) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($attribute->name, $attributeClass)) {
                    continue;
                }
                $foundAttributes[] = $attribute;
            }
        }
        return $foundAttributes;
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
    /**
     * @param Attribute[] $dataProviderAttributes
     * @return string[]
     */
    private function resolveAttributeMethodNames(array $dataProviderAttributes) : array
    {
        $dataProviderMethodNames = [];
        foreach ($dataProviderAttributes as $dataProviderAttribute) {
            $methodName = $dataProviderAttribute->args[0]->value;
            if (!$methodName instanceof String_) {
                continue;
            }
            $dataProviderMethodNames[] = $methodName->value;
        }
        return $dataProviderMethodNames;
    }
}
