<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\PropertyPresenceChecker;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\ValueObject\PropertyMetadata;
final class ClassDependencyManipulator
{
    /**
     * @var ClassMethodAssignManipulator
     */
    private $classMethodAssignManipulator;
    /**
     * @var NodeFactory
     */
    private $nodeFactory;
    /**
     * @var ChildAndParentClassManipulator
     */
    private $childAndParentClassManipulator;
    /**
     * @var StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @var PropertyPresenceChecker
     */
    private $propertyPresenceChecker;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\NodeManipulator\ChildAndParentClassManipulator $childAndParentClassManipulator, \Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\Core\NodeManipulator\ClassMethodAssignManipulator $classMethodAssignManipulator, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\NodeManipulator\StmtsManipulator $stmtsManipulator, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\Core\NodeAnalyzer\PropertyPresenceChecker $propertyPresenceChecker, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->childAndParentClassManipulator = $childAndParentClassManipulator;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function addConstructorDependency(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : void
    {
        if ($this->hasClassPropertyAndDependency($class, $propertyMetadata)) {
            return;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->classInsertManipulator->addPropertyToClass($class, $propertyMetadata->getName(), $propertyMetadata->getType());
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->addPromotedProperty($class, $propertyMetadata);
        } else {
            $assign = $this->nodeFactory->createPropertyAssignment($propertyMetadata->getName());
            $this->addConstructorDependencyWithCustomAssign($class, $propertyMetadata->getName(), $propertyMetadata->getType(), $assign);
        }
    }
    public function addConstructorDependencyWithCustomAssign(\PhpParser\Node\Stmt\Class_ $class, string $name, ?\PHPStan\Type\Type $type, \PhpParser\Node\Expr\Assign $assign) : void
    {
        /** @var ClassMethod|null $constructorMethod */
        $constructorMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($constructorMethod !== null) {
            $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructorMethod, $name, $type, $assign);
            return;
        }
        $constructorMethod = $this->nodeFactory->createPublicMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructorMethod, $name, $type, $assign);
        $this->classInsertManipulator->addAsFirstMethod($class, $constructorMethod);
        $this->childAndParentClassManipulator->completeParentConstructor($class, $constructorMethod);
        $this->childAndParentClassManipulator->completeChildConstructors($class, $constructorMethod);
    }
    /**
     * @param Stmt[] $stmts
     */
    public function addStmtsToConstructorIfNotThereYet(\PhpParser\Node\Stmt\Class_ $class, array $stmts) : void
    {
        $classMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $classMethod = $this->nodeFactory->createPublicMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
            // keep parent constructor call
            if ($this->hasClassParentClassMethod($class, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                $classMethod->stmts[] = $this->createParentClassMethodCall(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
            }
            $classMethod->stmts = \array_merge((array) $classMethod->stmts, $stmts);
            $class->stmts = \array_merge($class->stmts, [$classMethod]);
            return;
        }
        $stmts = $this->stmtsManipulator->filterOutExistingStmts($classMethod, $stmts);
        // all stmts are already there → skip
        if ($stmts === []) {
            return;
        }
        $classMethod->stmts = \array_merge($stmts, (array) $classMethod->stmts);
    }
    public function addInjectProperty(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : void
    {
        if ($this->propertyPresenceChecker->hasClassContextPropertyByName($class, $propertyMetadata->getName())) {
            return;
        }
        $this->classInsertManipulator->addInjectPropertyToClass($class, $propertyMetadata);
    }
    private function addPromotedProperty(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : void
    {
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $param = $this->nodeFactory->createPromotedPropertyParam($propertyMetadata);
        if ($constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            // parameter is already added
            if ($this->hasMethodParameter($constructClassMethod, $propertyMetadata->getName())) {
                return;
            }
            $constructClassMethod->params[] = $param;
        } else {
            $constructClassMethod = $this->nodeFactory->createPublicMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
            $constructClassMethod->params[] = $param;
            $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
        }
        $this->childAndParentClassManipulator->completeParentConstructor($class, $constructClassMethod);
        $this->childAndParentClassManipulator->completeChildConstructors($class, $constructClassMethod);
    }
    private function hasClassParentClassMethod(\PhpParser\Node\Stmt\Class_ $class, string $methodName) : bool
    {
        $scope = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    private function createParentClassMethodCall(string $methodName) : \PhpParser\Node\Stmt\Expression
    {
        $staticCall = new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name('parent'), $methodName);
        return new \PhpParser\Node\Stmt\Expression($staticCall);
    }
    private function isParamInConstructor(\PhpParser\Node\Stmt\Class_ $class, string $propertyName) : bool
    {
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        foreach ($constructClassMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    private function hasClassPropertyAndDependency(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : bool
    {
        if (!$this->propertyPresenceChecker->hasClassContextPropertyByName($class, $propertyMetadata->getName())) {
            return \false;
        }
        return $this->isParamInConstructor($class, $propertyMetadata->getName());
    }
    private function hasMethodParameter(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $name) : bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return \true;
            }
        }
        return \false;
    }
}
