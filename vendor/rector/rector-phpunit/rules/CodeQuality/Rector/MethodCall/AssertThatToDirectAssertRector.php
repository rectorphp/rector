<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertThatToDirectAssertRector\AssertThatToDirectAssertRectorTest
 */
final class AssertThatToDirectAssertRector extends AbstractRector
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
        return new RuleDefinition('Change $this->assertThat($value, $this->*()) to direct $this->assert*() method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertThat($value, $this->isTrue());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertTrue($value);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertThat'])) {
            return null;
        }
        $secondArg = $node->getArgs()[1];
        if (!$secondArg->value instanceof MethodCall) {
            return null;
        }
        $exactAssertMethodCall = $secondArg->value;
        $exactAssertName = $this->getName($exactAssertMethodCall->name);
        if ($exactAssertName === 'equalTo') {
            $node->name = new Identifier('assertEquals');
            $node->args[1] = $node->args[0];
            $node->args[0] = $exactAssertMethodCall->args[0];
            return $node;
        }
        if ($exactAssertName === 'isTrue') {
            $node->name = new Identifier('assertTrue');
            unset($node->args[1]);
            return $node;
        }
        return null;
    }
}
