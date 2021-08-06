<?php

declare(strict_types=1);

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
    public function __construct(
        private ClassInsertManipulator $classInsertManipulator,
        private ClassMethodAssignManipulator $classMethodAssignManipulator,
        private NodeFactory $nodeFactory,
        private StmtsManipulator $stmtsManipulator,
        private PhpVersionProvider $phpVersionProvider,
        private PropertyPresenceChecker $propertyPresenceChecker,
        private NodeNameResolver $nodeNameResolver,
        private NodesToRemoveCollector $nodesToRemoveCollector,
        private AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer,
        private DependencyClassMethodDecorator $dependencyClassMethodDecorator
    ) {
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

        if ($this->shouldAddPromotedProperty($class, $propertyMetadata)) {
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

        /** @var Scope $scope */
        $scope = $class->getAttribute(AttributeKey::SCOPE);

        $this->dependencyClassMethodDecorator->decorateConstructorWithParentDependencies(
            $class,
            $constructorMethod,
            $scope
        );
    }

    /**
     * @param Stmt[] $stmts
     */
    public function addStmtsToConstructorIfNotThereYet(Class_ $class, array $stmts): void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);

        if (! $classMethod instanceof ClassMethod) {
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
        if ($this->propertyPresenceChecker->hasClassContextProperty($class, $propertyMetadata)) {
            return;
        }

        $this->classInsertManipulator->addInjectPropertyToClass($class, $propertyMetadata);
    }

    private function addPromotedProperty(Class_ $class, PropertyMetadata $propertyMetadata): void
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        $param = $this->nodeFactory->createPromotedPropertyParam($propertyMetadata);

        if ($constructClassMethod instanceof ClassMethod) {
            // parameter is already added
            if ($this->hasMethodParameter($constructClassMethod, $propertyMetadata->getName())) {
                return;
            }

            $constructClassMethod->params[] = $param;
        } else {
            $constructClassMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);
            $constructClassMethod->params[] = $param;
            $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
        }

        /** @var Scope $scope */
        $scope = $class->getAttribute(AttributeKey::SCOPE);

        $this->dependencyClassMethodDecorator->decorateConstructorWithParentDependencies(
            $class,
            $constructClassMethod,
            $scope
        );
    }

    private function hasClassParentClassMethod(Class_ $class, string $methodName): bool
    {
        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
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
        $property = $this->propertyPresenceChecker->getClassContextProperty($class, $propertyMetadata);
        if ($property === null) {
            return false;
        }

        if (! $this->autowiredClassMethodOrPropertyAnalyzer->detect($property)) {
            return $this->isParamInConstructor($class, $propertyMetadata->getName());
        }

        // is inject/autowired property?
        return $property instanceof Property && $this->autowiredClassMethodOrPropertyAnalyzer->detect($property);
    }

    private function hasMethodParameter(ClassMethod $classMethod, string $name): bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return true;
            }
        }

        return false;
    }

    private function shouldAddPromotedProperty(Class_ $class, PropertyMetadata $propertyMetadata): bool
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            return false;
        }
        // only if the property does not exist yet
        $existingProperty = $class->getProperty($propertyMetadata->getName());
        if (! $existingProperty instanceof Property) {
            return true;
        }

        return $this->nodesToRemoveCollector->isNodeRemoved($existingProperty);
    }
}
