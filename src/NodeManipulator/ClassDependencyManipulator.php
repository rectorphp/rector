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
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\PropertyPresenceChecker;
use Rector\Core\NodeManipulator\Dependency\DependencyClassMethodDecorator;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer;
final class ClassDependencyManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodAssignManipulator
     */
    private $classMethodAssignManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyPresenceChecker
     */
    private $propertyPresenceChecker;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer
     */
    private $autowiredClassMethodOrPropertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\Dependency\DependencyClassMethodDecorator
     */
    private $dependencyClassMethodDecorator;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\Core\NodeManipulator\ClassMethodAssignManipulator $classMethodAssignManipulator, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\NodeManipulator\StmtsManipulator $stmtsManipulator, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\Core\NodeAnalyzer\PropertyPresenceChecker $propertyPresenceChecker, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector, \Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer, \Rector\Core\NodeManipulator\Dependency\DependencyClassMethodDecorator $dependencyClassMethodDecorator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->autowiredClassMethodOrPropertyAnalyzer = $autowiredClassMethodOrPropertyAnalyzer;
        $this->dependencyClassMethodDecorator = $dependencyClassMethodDecorator;
    }
    public function addConstructorDependency(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : void
    {
        if ($this->hasClassPropertyAndDependency($class, $propertyMetadata)) {
            return;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->classInsertManipulator->addPropertyToClass($class, $propertyMetadata->getName(), $propertyMetadata->getType());
        }
        if ($this->shouldAddPromotedProperty($class, $propertyMetadata)) {
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
        /** @var Scope $scope */
        $scope = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $this->dependencyClassMethodDecorator->decorateConstructorWithParentDependencies($class, $constructorMethod, $scope);
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
        if ($this->propertyPresenceChecker->hasClassContextProperty($class, $propertyMetadata)) {
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
        /** @var Scope $scope */
        $scope = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $this->dependencyClassMethodDecorator->decorateConstructorWithParentDependencies($class, $constructClassMethod, $scope);
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
        $staticCall = new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::PARENT()->getValue()), $methodName);
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
        $property = $this->propertyPresenceChecker->getClassContextProperty($class, $propertyMetadata);
        if ($property === null) {
            return \false;
        }
        if (!$this->autowiredClassMethodOrPropertyAnalyzer->detect($property)) {
            return $this->isParamInConstructor($class, $propertyMetadata->getName());
        }
        // is inject/autowired property?
        return $property instanceof \PhpParser\Node\Stmt\Property;
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
    private function shouldAddPromotedProperty(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : bool
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION)) {
            return \false;
        }
        // only if the property does not exist yet
        $existingProperty = $class->getProperty($propertyMetadata->getName());
        if (!$existingProperty instanceof \PhpParser\Node\Stmt\Property) {
            return \true;
        }
        return $this->nodesToRemoveCollector->isNodeRemoved($existingProperty);
    }
}
