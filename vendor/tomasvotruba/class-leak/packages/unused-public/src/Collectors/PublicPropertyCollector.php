<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors;

use RectorPrefix202608\Livewire\Component;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ApiDocStmtAnalyzer;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Configuration;
/**
 * @implements Collector<InClassNode, non-empty-array<array{class-string, string, int}>>
 */
final class PublicPropertyCollector implements Collector
{
    /**
     * @readonly
     */
    private ApiDocStmtAnalyzer $apiDocStmtAnalyzer;
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @var array<string>
     */
    private const CLASSES_TO_SKIP = [Component::class];
    public function __construct(ApiDocStmtAnalyzer $apiDocStmtAnalyzer, Configuration $configuration)
    {
        $this->apiDocStmtAnalyzer = $apiDocStmtAnalyzer;
        $this->configuration = $configuration;
    }
    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }
    /**
     * @param InClassNode $node
     * @return non-empty-array<array{string, string, int}>|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->isUnusedPropertyEnabled()) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $classLike = $node->getOriginalNode();
        if (!$classLike instanceof Class_) {
            return null;
        }
        if ($this->shouldSkipClass($classReflection, $classLike)) {
            return null;
        }
        $publicPropertyNames = [];
        // Collect traditional public properties
        foreach ($classLike->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            foreach ($property->props as $propertyProperty) {
                $propertyName = $propertyProperty->name->toString();
                if ($this->shouldSkipProperty($classReflection, $propertyName, $scope)) {
                    continue;
                }
                $publicPropertyNames[] = [$classReflection->getName(), $propertyName, $node->getLine()];
            }
        }
        // Collect constructor promoted public properties
        foreach ($classLike->getMethods() as $classMethod) {
            if (!$this->isConstructorMethod($classMethod)) {
                continue;
            }
            foreach ($classMethod->getParams() as $param) {
                if (!$this->isPublicPromotedProperty($param)) {
                    continue;
                }
                if (!$param->var instanceof Variable) {
                    continue;
                }
                $propertyName = $param->var->name;
                if (!is_string($propertyName)) {
                    continue;
                }
                if ($this->shouldSkipProperty($classReflection, $propertyName, $scope)) {
                    continue;
                }
                $publicPropertyNames[] = [$classReflection->getName(), $propertyName, $param->getLine()];
            }
        }
        if ($publicPropertyNames === []) {
            return null;
        }
        return $publicPropertyNames;
    }
    private function isConstructorMethod(ClassMethod $classMethod): bool
    {
        return $classMethod->name->toLowerString() === '__construct';
    }
    private function isPublicPromotedProperty(Param $param): bool
    {
        // Check if parameter has a visibility flag (promoted property)
        if ($param->flags === 0) {
            return \false;
        }
        // Check if it's public (Class_::MODIFIER_PUBLIC = 1)
        return ($param->flags & Class_::MODIFIER_PUBLIC) !== 0;
    }
    private function shouldSkipProperty(ClassReflection $classReflection, string $propertyName, Scope $scope): bool
    {
        if (!$classReflection->hasProperty($propertyName)) {
            return \false;
        }
        $extendedPropertyReflection = $classReflection->getProperty($propertyName, $scope);
        // don't inherit doc from a private property
        if ($extendedPropertyReflection->isPrivate()) {
            return \false;
        }
        $docComment = $extendedPropertyReflection->getDocComment();
        if ($docComment !== null && $this->apiDocStmtAnalyzer->isApiDocComment($docComment)) {
            return \true;
        }
        $parentClassReflection = $classReflection->getParentClass();
        foreach ($classReflection->getInterfaces() as $interface) {
            if ($interface->hasProperty($propertyName)) {
                return \true;
            }
        }
        if (!$parentClassReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->shouldSkipProperty($parentClassReflection, $propertyName, $scope);
    }
    private function shouldSkipClass(ClassReflection $classReflection, Class_ $class): bool
    {
        foreach (self::CLASSES_TO_SKIP as $classToSkip) {
            if ($classReflection->isSubclassOf($classToSkip)) {
                return \true;
            }
        }
        return $this->apiDocStmtAnalyzer->isApiDoc($class, $classReflection);
    }
}
