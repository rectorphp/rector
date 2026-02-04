<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\MethodCall\ExplicitMockExpectsCallRector\ExplicitMockExpectsCallRectorTest
 */
final class ExplicitMockExpectsCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add explicit expects() to method() mock calls, to make expectations count explicit', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testMe()
    {
        $someMock = $this->createMock(\stdClass::class);
        $someMock->method('some');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testMe()
    {
        $someMock = $this->createMock(\stdClass::class);
        $someMock->expects($this->atLeastOnce())->method('some');
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isTestClassMethod($node)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (&$hasChanged): ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->isName($node->name, 'method')) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType(PHPUnitClassName::MOCK_OBJECT))) {
                return null;
            }
            $node->var = new MethodCall($node->var, 'expects', [new Arg(new MethodCall(new Variable('this'), 'atLeastOnce'))]);
            $hasChanged = \true;
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
