<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeVisitor;
use PHPStan\Type\ObjectType;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\NullableObjectAssignCollector;
use Rector\PHPUnit\CodeQuality\NodeFactory\AssertMethodCallFactory;
use Rector\PHPUnit\CodeQuality\TypeAnalyzer\MethodCallParameterTypeResolver;
use Rector\PHPUnit\CodeQuality\TypeAnalyzer\SimpleTypeAnalyzer;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToTypeCollection;
use Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\AddInstanceofAssertForNullableArgumentRector\AddInstanceofAssertForNullableArgumentRectorTest
 */
final class AddInstanceofAssertForNullableArgumentRector extends AbstractRector
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
    /**
     * @readonly
     */
    private MethodCallParameterTypeResolver $methodCallParameterTypeResolver;
    /**
     * @readonly
     */
    private AssertCallAnalyzer $assertCallAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, NullableObjectAssignCollector $nullableObjectAssignCollector, AssertMethodCallFactory $assertMethodCallFactory, MethodCallParameterTypeResolver $methodCallParameterTypeResolver, AssertCallAnalyzer $assertCallAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->nullableObjectAssignCollector = $nullableObjectAssignCollector;
        $this->assertMethodCallFactory = $assertMethodCallFactory;
        $this->methodCallParameterTypeResolver = $methodCallParameterTypeResolver;
        $this->assertCallAnalyzer = $assertCallAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add explicit instance assert between above nullable object pass', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someObject = $this->getSomeObject();

        $this->process($someObject);
    }

    private function getSomeObject(): ?SomeClass
    {
        if (mt_rand(0, 1)) {
            return new SomeClass();
        }

        return null;
    }

    private function process(SomeClass $someObject): void
    {
        // non-nullable use here
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

        $this->process($someObject);
    }

    private function getSomeObject(): ?SomeClass
    {
        if (mt_rand(0, 1)) {
            return new SomeClass();
        }

        return null;
    }

    private function process(SomeClass $someObject): void
    {
        // non-nullable use here
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
        if ($node->stmts === null || count($node->stmts) < 2) {
            return null;
        }
        $hasChanged = \false;
        $variableNameToTypeCollection = $this->nullableObjectAssignCollector->collect($node);
        $next = 0;
        foreach ($node->stmts as $key => $stmt) {
            // has callable on nullable variable of already collected name?
            $matchedNullableVariableNameToType = $this->matchedNullableArgumentNameToType($stmt, $variableNameToTypeCollection);
            if (!$matchedNullableVariableNameToType instanceof VariableNameToType) {
                continue;
            }
            // adding type here + popping the variable name out
            $assertInstanceOfExpression = $this->assertMethodCallFactory->createAssertInstanceOf($matchedNullableVariableNameToType);
            array_splice($node->stmts, $key + $next, 0, [$assertInstanceOfExpression]);
            // remove variable name from nullable ones
            $hasChanged = \true;
            // from now on, the variable is not nullable, remove to avoid double asserts
            $variableNameToTypeCollection->remove($matchedNullableVariableNameToType);
            ++$next;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function matchedNullableArgumentNameToType(Stmt $stmt, VariableNameToTypeCollection $variableNameToTypeCollection): ?VariableNameToType
    {
        $matchedNullableVariableNameToType = null;
        $this->traverseNodesWithCallable($stmt, function (Node $node) use ($variableNameToTypeCollection, &$matchedNullableVariableNameToType): ?int {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            // avoid double null on assert
            if ($this->assertCallAnalyzer->isAssertMethodCall($node)) {
                return null;
            }
            $classMethodParameterTypes = $this->methodCallParameterTypeResolver->resolve($node);
            foreach ($node->getArgs() as $position => $arg) {
                if (!$arg->value instanceof Variable) {
                    continue;
                }
                $variableType = $this->getType($arg->value);
                if (!SimpleTypeAnalyzer::isNullableType($variableType)) {
                    return null;
                }
                // should not happen
                if (!isset($classMethodParameterTypes[$position])) {
                    return null;
                }
                $variableName = $this->getName($arg->value);
                if (!is_string($variableName)) {
                    return null;
                }
                $matchedNullableVariableNameToType = $variableNameToTypeCollection->matchByVariableName($variableName);
                // is object type required?
                if (!$classMethodParameterTypes[$position] instanceof ObjectType) {
                    return null;
                }
                return NodeVisitor::STOP_TRAVERSAL;
            }
            return null;
        });
        return $matchedNullableVariableNameToType;
    }
}
