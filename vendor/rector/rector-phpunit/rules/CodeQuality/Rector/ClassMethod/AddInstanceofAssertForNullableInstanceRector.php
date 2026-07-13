<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\NullableObjectAssignCollector;
use Rector\PHPUnit\CodeQuality\NodeFactory\AssertMethodCallFactory;
use Rector\PHPUnit\CodeQuality\TypeAnalyzer\SimpleTypeAnalyzer;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToTypeCollection;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\AddInstanceofAssertForNullableInstanceRector\AddInstanceofAssertForNullableInstanceRectorTest
 */
final class AddInstanceofAssertForNullableInstanceRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private NullableObjectAssignCollector $nullableObjectAssignCollector;
    /**
     * @readonly
     */
    private AssertMethodCallFactory $assertMethodCallFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, NullableObjectAssignCollector $nullableObjectAssignCollector, AssertMethodCallFactory $assertMethodCallFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->nullableObjectAssignCollector = $nullableObjectAssignCollector;
        $this->assertMethodCallFactory = $assertMethodCallFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add explicit instance assert between nullable object assign and method call on nullable object (spotted by PHPStan)', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someObject = $this->getSomeObject();

        $value = $someObject->getSomeMethod();
    }

    private function getSomeObject(): ?SomeClass
    {
        if (mt_rand(0, 1)) {
            return new SomeClass();
        }

        return null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someObject = $this->getSomeObject();
        $this->assertInstanceOf(SomeClass::class, $someObject);

        $value = $someObject->getSomeMethod();
    }

    private function getSomeObject(): ?SomeClass
    {
        if (mt_rand(0, 1)) {
            return new SomeClass();
        }

        return null;
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
        return [ClassMethod::class, Foreach_::class];
    }
    /**
     * @param ClassMethod|Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->stmts === [] || $node->stmts === null || count($node->stmts) < 2) {
            return null;
        }
        $hasChanged = \false;
        $variableNameToTypeCollection = $this->nullableObjectAssignCollector->collect($node);
        $next = 0;
        foreach ($node->stmts as $key => $stmt) {
            // has callable on nullable variables of already collected names?
            // a single statement can use multiple nullable variables at once
            $matchedNullableVariableNameToTypes = $this->matchNullableVariableNameToTypes($stmt, $variableNameToTypeCollection);
            foreach ($matchedNullableVariableNameToTypes as $matchedNullableVariableNameToType) {
                // adding type here + popping the variable name out
                $assertInstanceOfExpression = $this->assertMethodCallFactory->createAssertInstanceOf($matchedNullableVariableNameToType);
                array_splice($node->stmts, $key + $next, 0, [$assertInstanceOfExpression]);
                $hasChanged = \true;
                // from now on, the variable is not nullable, remove to avoid double asserts
                $variableNameToTypeCollection->remove($matchedNullableVariableNameToType);
                ++$next;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return VariableNameToType[]
     */
    private function matchNullableVariableNameToTypes(Stmt $stmt, VariableNameToTypeCollection $variableNameToTypeCollection): array
    {
        $matchedNullableVariableNameToTypes = [];
        $matchedVariableNames = [];
        $this->traverseNodesWithCallable($stmt, function (Node $node) use ($variableNameToTypeCollection, &$matchedNullableVariableNameToTypes, &$matchedVariableNames): ?int {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            $variableType = $this->getType($node->var);
            if (!SimpleTypeAnalyzer::isNullableType($variableType)) {
                return null;
            }
            $variableName = $this->getName($node->var);
            if ($variableName === null) {
                return null;
            }
            // avoid duplicated assert for the same variable used multiple times in one statement
            if (in_array($variableName, $matchedVariableNames, \true)) {
                return null;
            }
            $matchedNullableVariableNameToType = $variableNameToTypeCollection->matchByVariableName($variableName);
            if (!$matchedNullableVariableNameToType instanceof VariableNameToType) {
                return null;
            }
            $matchedVariableNames[] = $variableName;
            $matchedNullableVariableNameToTypes[] = $matchedNullableVariableNameToType;
            return null;
        });
        return $matchedNullableVariableNameToTypes;
    }
}
