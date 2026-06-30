<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\StubPropertyResolver;
use Rector\PHPUnit\CodeQuality\NodeFinder\PropertyFetchUsageFinder;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\InlineStubPropertyToCreateStubMethodCallRector\InlineStubPropertyToCreateStubMethodCallRectorTest
 */
final class InlineStubPropertyToCreateStubMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PropertyFetchFinder $propertyFetchFinder;
    /**
     * @readonly
     */
    private PropertyFetchUsageFinder $propertyFetchUsageFinder;
    /**
     * @readonly
     */
    private StubPropertyResolver $stubPropertyResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PropertyFetchFinder $propertyFetchFinder, PropertyFetchUsageFinder $propertyFetchUsageFinder, StubPropertyResolver $stubPropertyResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->propertyFetchUsageFinder = $propertyFetchUsageFinder;
        $this->stubPropertyResolver = $stubPropertyResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Inline stub property only used to pass as new argument to a method call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;

final class SomeTest extends TestCase
{
    private Stub $someStub;

    protected function setUp(): void
    {
        $this->someStub = $this->createStub(SomeClass::class);
    }

    public function testAnother()
    {
        $anotherObject = new AnotherObject($this->someStub);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testAnother()
    {
        $anotherObject = new AnotherObject($this->createStub(SomeStub::class));
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
        $propertyNamesToStubClasses = $this->stubPropertyResolver->resolveFromClassMethod($setUpClassMethod);
        if ($propertyNamesToStubClasses === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            $soleProperty = $this->matchSoleStubPropertyItem($stmt);
            if (!$soleProperty instanceof PropertyItem) {
                continue;
            }
            $propertyName = $this->getName($soleProperty);
            if (!isset($propertyNamesToStubClasses[$propertyName])) {
                continue;
            }
            $currentPropertyFetchesInNewArgs = $this->propertyFetchUsageFinder->findInCallLikes($node, $propertyName);
            $currentPropertyFetchesInArrays = $this->propertyFetchUsageFinder->findInArrays($node, $propertyName);
            // are there more uses than simple passing to a new instance?
            $totalPropertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($node, $propertyName);
            if (count($totalPropertyFetches) - 1 !== count($currentPropertyFetchesInNewArgs) + count($currentPropertyFetchesInArrays)) {
                continue;
            }
            $hasChanged = \true;
            // 1. remove property
            unset($node->stmts[$key]);
            // 2. remove property assign in setUp()
            $this->removeSetupPropertyFetchByPropertyName($setUpClassMethod, $propertyName);
            // 3. replace property fetch calls, with createStub()
            $stubClassName = $propertyNamesToStubClasses[$propertyName];
            foreach ($node->getMethods() as $classMethod) {
                $this->refactorClassMethod($classMethod, $propertyName, $stubClassName);
            }
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
    private function refactorClassMethod(ClassMethod $classMethod, string $propertyName, string $stubClassName): void
    {
        $propertyFetches = $this->betterNodeFinder->find((array) $classMethod->stmts, fn(Node $node): bool => $node instanceof PropertyFetch && $this->isName($node->name, $propertyName));
        if ($propertyFetches === []) {
            return;
        }
        // single use → inline directly
        if (count($propertyFetches) === 1) {
            $this->traverseNodesWithCallable($classMethod, function (Node $node) use ($propertyName, $stubClassName): ?MethodCall {
                if (!$node instanceof PropertyFetch) {
                    return null;
                }
                if (!$this->isName($node->name, $propertyName)) {
                    return null;
                }
                return $this->createCreateStubMethodCall($stubClassName);
            });
            return;
        }
        // multiple uses → assign to a local variable above first use and reuse it
        $variableName = $this->resolveVariableName($propertyName);
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use ($propertyName, $variableName): ?Variable {
            if (!$node instanceof PropertyFetch) {
                return null;
            }
            if (!$this->isName($node->name, $propertyName)) {
                return null;
            }
            return new Variable($variableName);
        });
        $assignExpression = new Expression(new Assign(new Variable($variableName), $this->createCreateStubMethodCall($stubClassName)));
        $this->insertBeforeFirstVariableUse($classMethod, $variableName, $assignExpression);
    }
    private function insertBeforeFirstVariableUse(ClassMethod $classMethod, string $variableName, Expression $assignExpression): void
    {
        $stmts = (array) $classMethod->stmts;
        foreach ($stmts as $key => $stmt) {
            $firstVariable = $this->betterNodeFinder->findFirst($stmt, fn(Node $node): bool => $node instanceof Variable && $this->isName($node, $variableName));
            if (!$firstVariable instanceof Variable) {
                continue;
            }
            array_splice($stmts, $key, 0, [$assignExpression]);
            $classMethod->stmts = $stmts;
            return;
        }
    }
    private function createCreateStubMethodCall(string $stubClassName): MethodCall
    {
        $classConstFetch = new ClassConstFetch(new FullyQualified($stubClassName), 'class');
        return new MethodCall(new Variable('this'), new Identifier('createStub'), [new Arg($classConstFetch)]);
    }
    private function resolveVariableName(string $propertyName): string
    {
        if (substr_compare(strtolower($propertyName), 'stub', -strlen('stub')) === 0) {
            return $propertyName;
        }
        return $propertyName . 'Stub';
    }
    private function removeSetupPropertyFetchByPropertyName(ClassMethod $setUpClassMethod, string $propertyName): void
    {
        foreach ((array) $setUpClassMethod->stmts as $key => $setupStmt) {
            if (!$setupStmt instanceof Expression) {
                continue;
            }
            if (!$setupStmt->expr instanceof Assign) {
                continue;
            }
            $assign = $setupStmt->expr;
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            if (!$this->isName($propertyFetch->name, $propertyName)) {
                continue;
            }
            unset($setUpClassMethod->stmts[$key]);
        }
    }
    private function matchSoleStubPropertyItem(Property $property): ?PropertyItem
    {
        if (count($property->props) > 1) {
            return null;
        }
        // we need some type
        if (!$property->type instanceof Node) {
            return null;
        }
        if (!$this->isObjectType($property->type, new ObjectType(PHPUnitClassName::STUB))) {
            return null;
        }
        return $property->props[0];
    }
}
