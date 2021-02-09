<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
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

    public function __construct(
        ChildAndParentClassManipulator $childAndParentClassManipulator,
        ClassInsertManipulator $classInsertManipulator,
        ClassMethodAssignManipulator $classMethodAssignManipulator,
        NodeFactory $nodeFactory,
        StmtsManipulator $stmtsManipulator,
        PhpVersionProvider $phpVersionProvider,
        PropertyPresenceChecker $propertyPresenceChecker,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->childAndParentClassManipulator = $childAndParentClassManipulator;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function addConstructorDependency(Class_ $class, PropertyMetadata $propertyMetadata): void
    {
        if ($this->hasClassPropertyAndDependency($class, $propertyMetadata)) {
            return;
        }

        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->classInsertManipulator->addPropertyToClass(
                $class,
                $propertyMetadata->getName(),
                $propertyMetadata->getType()
            );
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->addPromotedProperty($class, $propertyMetadata);
        } else {
            $assign = $this->nodeFactory->createPropertyAssignment($propertyMetadata->getName());
            $this->addConstructorDependencyWithCustomAssign(
                $class,
                $propertyMetadata->getName(),
                $propertyMetadata->getType(),
                $assign
            );
        }
    }

    public function addConstructorDependencyWithCustomAssign(
        Class_ $class,
        string $name,
        ?Type $type,
        Assign $assign
    ): void {
        /** @var ClassMethod|null $constructorMethod */
        $constructorMethod = $class->getMethod(MethodName::CONSTRUCT);

        if ($constructorMethod !== null) {
            $this->classMethodAssignManipulator->addParameterAndAssignToMethod(
                $constructorMethod,
                $name,
                $type,
                $assign
            );
            return;
        }

        $constructorMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);

        $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructorMethod, $name, $type, $assign);
        $this->classInsertManipulator->addAsFirstMethod($class, $constructorMethod);

        $this->childAndParentClassManipulator->completeParentConstructor($class, $constructorMethod);
        $this->childAndParentClassManipulator->completeChildConstructors($class, $constructorMethod);
    }

    /**
     * @param Stmt[] $stmts
     */
    public function addStmtsToConstructorIfNotThereYet(Class_ $class, array $stmts): void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);

        if ($classMethod === null) {
            $classMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);

            // keep parent constructor call
            if ($this->hasClassParentClassMethod($class, MethodName::CONSTRUCT)) {
                $classMethod->stmts[] = $this->createParentClassMethodCall(MethodName::CONSTRUCT);
            }

            $classMethod->stmts = array_merge((array) $classMethod->stmts, $stmts);

            $class->stmts = array_merge($class->stmts, [$classMethod]);
            return;
        }

        $stmts = $this->stmtsManipulator->filterOutExistingStmts($classMethod, $stmts);

        // all stmts are already there → skip
        if ($stmts === []) {
            return;
        }

        $classMethod->stmts = array_merge($stmts, (array) $classMethod->stmts);
    }

    public function addInjectProperty(Class_ $class, PropertyMetadata $propertyMetadata): void
    {
        if ($this->propertyPresenceChecker->hasClassContextPropertyByName($class, $propertyMetadata->getName())) {
            return;
        }

        $this->classInsertManipulator->addInjectPropertyToClass($class, $propertyMetadata);
    }

    private function addPromotedProperty(Class_ $class, PropertyMetadata $propertyMetadata): void
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        $param = $this->nodeFactory->createPromotedPropertyParam($propertyMetadata);

        if ($constructClassMethod instanceof ClassMethod) {
            $constructClassMethod->params[] = $param;
        } else {
            $constructClassMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);
            $constructClassMethod->params[] = $param;
            $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
        }

        $this->childAndParentClassManipulator->completeParentConstructor($class, $constructClassMethod);
        $this->childAndParentClassManipulator->completeChildConstructors($class, $constructClassMethod);
    }

    private function hasClassParentClassMethod(Class_ $class, string $methodName): bool
    {
        $parentClassName = $class->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return false;
        }

        return method_exists($parentClassName, $methodName);
    }

    private function createParentClassMethodCall(string $methodName): Expression
    {
        $staticCall = new StaticCall(new Name('parent'), $methodName);

        return new Expression($staticCall);
    }

    private function isParamInConstructor(Class_ $class, string $propertyName): bool
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return false;
        }

        foreach ($constructClassMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    private function hasClassPropertyAndDependency(Class_ $class, PropertyMetadata $propertyMetadata): bool
    {
        if (! $this->propertyPresenceChecker->hasClassContextPropertyByName($class, $propertyMetadata->getName())) {
            return false;
        }

        return $this->isParamInConstructor($class, $propertyMetadata->getName());
    }
}
