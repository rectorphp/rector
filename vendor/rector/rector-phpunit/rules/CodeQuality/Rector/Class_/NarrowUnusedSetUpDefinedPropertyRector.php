<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeManipulator\PropertyManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\NarrowUnusedSetUpDefinedPropertyRector\NarrowUnusedSetUpDefinedPropertyRectorTest
 */
final class NarrowUnusedSetUpDefinedPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private PropertyManipulator $propertyManipulator;
    /**
     * @readonly
     */
    private NodeFinder $nodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ReflectionResolver $reflectionResolver, PropertyManipulator $propertyManipulator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->nodeFinder = new NodeFinder();
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn property used only in setUp() to variable', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    private $someServiceMock;

    public function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    public function setUp(): void
    {
        $someServiceMock = $this->createMock(SomeService::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        $isFinalClass = $node->isFinal();
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        foreach ($node->stmts as $key => $classStmt) {
            if (!$classStmt instanceof Property) {
                continue;
            }
            $property = $classStmt;
            if (count($property->props) !== 1) {
                continue;
            }
            $propertyName = $property->props[0]->name->toString();
            if ($this->shouldSkipProperty($isFinalClass, $property, $classReflection, $propertyName)) {
                continue;
            }
            if ($this->isPropertyUsedOutsideSetUpClassMethod($node, $setUpClassMethod, $property)) {
                continue;
            }
            // referenced inside a nested closure that may run after setUp() - turning it into a local
            // variable would leave it out of the closure scope, as there is no "use" binding to add it
            if ($this->isPropertyUsedInUnsafeNestedFunction($setUpClassMethod, $propertyName)) {
                continue;
            }
            $hasChanged = \true;
            unset($node->stmts[$key]);
            // change property to variable in setUp() method
            $this->traverseNodesWithCallable($setUpClassMethod, function (Node $node) use ($propertyName): ?Variable {
                if (!$node instanceof PropertyFetch) {
                    return null;
                }
                if (!$this->isName($node->var, 'this')) {
                    return null;
                }
                if (!$this->isName($node->name, $propertyName)) {
                    return null;
                }
                return new Variable($propertyName);
            });
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isPropertyUsedOutsideSetUpClassMethod(Class_ $class, ClassMethod $setUpClassMethod, Property $property): bool
    {
        $isPropertyUsed = \false;
        $propertyName = $property->props[0]->name->toString();
        foreach ($class->getMethods() as $classMethod) {
            // skip setUp() method
            if ($classMethod === $setUpClassMethod) {
                continue;
            }
            // check if property is used anywhere else than setup
            $usedPropertyFetch = $this->nodeFinder->findFirst($classMethod, function (Node $node) use ($propertyName): bool {
                if (!$node instanceof PropertyFetch) {
                    return \false;
                }
                if (!$this->isName($node->var, 'this')) {
                    return \false;
                }
                return $this->isName($node->name, $propertyName);
            });
            if ($usedPropertyFetch instanceof PropertyFetch) {
                $isPropertyUsed = \true;
            }
        }
        return $isPropertyUsed;
    }
    private function isPropertyUsedInUnsafeNestedFunction(ClassMethod $setUpClassMethod, string $propertyName): bool
    {
        // a regular closure does not capture outer variables without an explicit "use" binding,
        // so turning the property into a local variable always leaves it undefined inside the closure
        foreach ($this->nodeFinder->findInstanceOf($setUpClassMethod, Closure::class) as $closure) {
            if ($this->refersToThisProperty($closure, $propertyName)) {
                return \true;
            }
        }
        // an arrow function auto-captures by value at definition time; that only matches the original
        // lazy "$this->property" read when the property assignment is already complete before the arrow
        // function is defined. A later or wrapping assignment (e.g. a self-referencing type definition)
        // would capture a not-yet-assigned variable
        foreach ($this->nodeFinder->findInstanceOf($setUpClassMethod, ArrowFunction::class) as $arrowFunction) {
            if (!$this->refersToThisProperty($arrowFunction, $propertyName)) {
                continue;
            }
            if (!$this->isPropertyAssignedBefore($setUpClassMethod, $propertyName, $arrowFunction)) {
                return \true;
            }
        }
        return \false;
    }
    private function refersToThisProperty(Node $node, string $propertyName): bool
    {
        $propertyFetch = $this->nodeFinder->findFirst($node, function (Node $subNode) use ($propertyName): bool {
            if (!$subNode instanceof PropertyFetch) {
                return \false;
            }
            if (!$this->isName($subNode->var, 'this')) {
                return \false;
            }
            return $this->isName($subNode->name, $propertyName);
        });
        return $propertyFetch instanceof PropertyFetch;
    }
    private function isPropertyAssignedBefore(ClassMethod $setUpClassMethod, string $propertyName, ArrowFunction $arrowFunction): bool
    {
        $arrowFunctionStartTokenPos = $arrowFunction->getStartTokenPos();
        $assignBefore = $this->nodeFinder->findFirst($setUpClassMethod, function (Node $node) use ($propertyName, $arrowFunctionStartTokenPos): bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            if (!$node->var instanceof PropertyFetch) {
                return \false;
            }
            if (!$this->isName($node->var->var, 'this')) {
                return \false;
            }
            if (!$this->isName($node->var->name, $propertyName)) {
                return \false;
            }
            // assignment fully completes before the arrow function starts
            return $node->getEndTokenPos() < $arrowFunctionStartTokenPos;
        });
        return $assignBefore instanceof Assign;
    }
    private function shouldSkipProperty(bool $isFinalClass, Property $property, ClassReflection $classReflection, string $propertyName): bool
    {
        // possibly used by child
        if (!$isFinalClass && !$property->isPrivate()) {
            return \true;
        }
        // possibly used for caching or re-use
        if ($property->isStatic()) {
            return \true;
        }
        if ($property->props[0]->default instanceof Expr) {
            return \true;
        }
        return $this->propertyManipulator->isUsedByTrait($classReflection, $propertyName);
    }
}
