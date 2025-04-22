<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
use Rector\NodeAnalyzer\PropertyPresenceChecker;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
/**
 * @see \Rector\Tests\NodeManipulator\ClassDependencyManipulatorTest
 */
final class ClassDependencyManipulator
{
    /**
     * @readonly
     */
    private \Rector\NodeManipulator\ClassInsertManipulator $classInsertManipulator;
    /**
     * @readonly
     */
    private \Rector\NodeManipulator\ClassMethodAssignManipulator $classMethodAssignManipulator;
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private \Rector\NodeManipulator\StmtsManipulator $stmtsManipulator;
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private PropertyPresenceChecker $propertyPresenceChecker;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    public function __construct(\Rector\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\NodeManipulator\ClassMethodAssignManipulator $classMethodAssignManipulator, NodeFactory $nodeFactory, \Rector\NodeManipulator\StmtsManipulator $stmtsManipulator, PhpVersionProvider $phpVersionProvider, PropertyPresenceChecker $propertyPresenceChecker, NodeNameResolver $nodeNameResolver, AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer, ReflectionResolver $reflectionResolver, AstResolver $astResolver)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->autowiredClassMethodOrPropertyAnalyzer = $autowiredClassMethodOrPropertyAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
    }
    public function addConstructorDependency(Class_ $class, PropertyMetadata $propertyMetadata) : void
    {
        // already has property as dependency? skip it
        if ($this->hasClassPropertyAndDependency($class, $propertyMetadata)) {
            return;
        }
        // special case for Symfony @required
        $autowireClassMethod = $this->autowiredClassMethodOrPropertyAnalyzer->matchAutowiredMethodInClass($class);
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION) || $autowireClassMethod instanceof ClassMethod) {
            $this->classInsertManipulator->addPropertyToClass($class, $propertyMetadata->getName(), $propertyMetadata->getType());
        }
        // in case of existing autowire method, re-use it
        if ($autowireClassMethod instanceof ClassMethod) {
            $assign = $this->nodeFactory->createPropertyAssignment($propertyMetadata->getName());
            $this->classMethodAssignManipulator->addParameterAndAssignToMethod($autowireClassMethod, $propertyMetadata->getName(), $propertyMetadata->getType(), $assign);
            return;
        }
        $constructClassMethod = $this->resolveConstruct($class);
        // add PHP 8.0 promoted property
        if ($this->shouldAddPromotedProperty($class, $propertyMetadata)) {
            $this->addPromotedProperty($class, $propertyMetadata, $constructClassMethod);
            return;
        }
        $assign = $this->nodeFactory->createPropertyAssignment($propertyMetadata->getName());
        $this->addConstructorDependencyWithCustomAssign($class, $propertyMetadata->getName(), $propertyMetadata->getType(), $assign);
    }
    /**
     * @api doctrine
     */
    public function addConstructorDependencyWithCustomAssign(Class_ $class, string $name, ?Type $type, Assign $assign) : void
    {
        /** @var ClassMethod|null $constructClassMethod */
        $constructClassMethod = $this->resolveConstruct($class);
        if ($constructClassMethod instanceof ClassMethod) {
            if (!$class->getMethod(MethodName::CONSTRUCT) instanceof ClassMethod) {
                $parentArgs = [];
                foreach ($constructClassMethod->params as $originalParam) {
                    $parentArgs[] = new Arg(new Variable((string) $this->nodeNameResolver->getName($originalParam->var)));
                }
                $constructClassMethod->stmts = [new Expression(new StaticCall(new Name(ObjectReference::PARENT), MethodName::CONSTRUCT, $parentArgs))];
                $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
                $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructClassMethod, $name, $type, $assign);
            } else {
                $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructClassMethod, $name, $type, $assign);
            }
            return;
        }
        $constructClassMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);
        $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructClassMethod, $name, $type, $assign);
        $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
    }
    /**
     * @api doctrine
     * @param Stmt[] $stmts
     */
    public function addStmtsToConstructorIfNotThereYet(Class_ $class, array $stmts) : void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            $classMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);
            // keep parent constructor call
            if ($this->hasClassParentClassMethod($class, MethodName::CONSTRUCT)) {
                $classMethod->stmts[] = $this->createParentClassMethodCall(MethodName::CONSTRUCT);
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
    private function resolveConstruct(Class_ $class) : ?ClassMethod
    {
        /** @var ClassMethod|null $constructorMethod */
        $constructorMethod = $class->getMethod(MethodName::CONSTRUCT);
        // exists in current class
        if ($constructorMethod instanceof ClassMethod) {
            return $constructorMethod;
        }
        // lookup parent, found first found (nearest parent constructor to follow)
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $ancestors = \array_filter($classReflection->getAncestors(), static fn(ClassReflection $ancestor): bool => $ancestor->getName() !== $classReflection->getName());
        foreach ($ancestors as $ancestor) {
            if (!$ancestor->hasNativeMethod(MethodName::CONSTRUCT)) {
                continue;
            }
            $parentClass = $this->astResolver->resolveClassFromClassReflection($ancestor);
            if (!$parentClass instanceof ClassLike) {
                continue;
            }
            $parentConstructorMethod = $parentClass->getMethod(MethodName::CONSTRUCT);
            if (!$parentConstructorMethod instanceof ClassMethod) {
                continue;
            }
            if ($parentConstructorMethod->isPrivate()) {
                // stop, nearest __construct() uses private visibility
                // which parent::__construct() will cause error
                break;
            }
            $constructorMethod = clone $parentConstructorMethod;
            // reprint parent method node to avoid invalid tokens
            $this->nodeFactory->createReprintedNode($constructorMethod);
            return $constructorMethod;
        }
        return null;
    }
    private function addPromotedProperty(Class_ $class, PropertyMetadata $propertyMetadata, ?ClassMethod $constructClassMethod) : void
    {
        $param = $this->nodeFactory->createPromotedPropertyParam($propertyMetadata);
        if ($constructClassMethod instanceof ClassMethod) {
            // parameter is already added
            if ($this->hasMethodParameter($constructClassMethod, $propertyMetadata->getName())) {
                return;
            }
            // found construct, but only on parent, add to current class
            if (!$class->getMethod(MethodName::CONSTRUCT) instanceof ClassMethod) {
                $parentArgs = [];
                foreach ($constructClassMethod->params as $originalParam) {
                    $parentArgs[] = new Arg(new Variable((string) $this->nodeNameResolver->getName($originalParam->var)));
                }
                $constructClassMethod->params[] = $param;
                $constructClassMethod->stmts = [new Expression(new StaticCall(new Name(ObjectReference::PARENT), MethodName::CONSTRUCT, $parentArgs))];
                $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
            } else {
                $constructClassMethod->params[] = $param;
            }
        } else {
            $constructClassMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);
            $constructClassMethod->params[] = $param;
            $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
        }
    }
    private function hasClassParentClassMethod(Class_ $class, string $methodName) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    private function createParentClassMethodCall(string $methodName) : Expression
    {
        $staticCall = new StaticCall(new Name(ObjectReference::PARENT), $methodName);
        return new Expression($staticCall);
    }
    private function isParamInConstructor(Class_ $class, string $propertyName) : bool
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return \false;
        }
        foreach ($constructClassMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    private function hasClassPropertyAndDependency(Class_ $class, PropertyMetadata $propertyMetadata) : bool
    {
        $property = $this->propertyPresenceChecker->getClassContextProperty($class, $propertyMetadata);
        if ($property === null) {
            return \false;
        }
        if (!$this->autowiredClassMethodOrPropertyAnalyzer->detect($property)) {
            return $this->isParamInConstructor($class, $propertyMetadata->getName());
        }
        // is inject/autowired property?
        return $property instanceof Property;
    }
    private function hasMethodParameter(ClassMethod $classMethod, string $name) : bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldAddPromotedProperty(Class_ $class, PropertyMetadata $propertyMetadata) : bool
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            return \false;
        }
        // only if the property does not exist yet
        $existingProperty = $class->getProperty($propertyMetadata->getName());
        return !$existingProperty instanceof Property;
    }
}
