<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\FlipAssertRector\FlipAssertRectorTest
 */
final class FlipAssertRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns accidentally flipped assert order to right one, with expected expr to left', [new CodeSample(<<<'CODE_SAMPLE'
<?php

namespace RectorPrefix202411;

use PHPUnit\Framework\TestCase;
class SomeTest extends TestCase
{
    public function test()
    {
        $result = '...';
        $this->assertSame($result, 'expected');
    }
}
\class_alias('SomeTest', 'SomeTest', \false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

namespace RectorPrefix202411;

use PHPUnit\Framework\TestCase;
class SomeTest extends TestCase
{
    public function test()
    {
        $result = '...';
        $this->assertSame('expected', $result);
    }
}
\class_alias('SomeTest', 'SomeTest', \false);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertEquals'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $secondArg = $node->getArgs()[1];
        // correct location
        if ($this->isScalarValue($firstArg->value)) {
            return null;
        }
        if (!$this->isScalarValue($secondArg->value)) {
            return null;
        }
        $oldArgs = $node->getArgs();
        // flip args
        [$oldArgs[0], $oldArgs[1]] = [$oldArgs[1], $oldArgs[0]];
        $node->args = $oldArgs;
        return $node;
    }
    private function isScalarValue(Expr $expr) : bool
    {
        if ($expr instanceof Scalar) {
            return \true;
        }
        return $expr instanceof ClassConstFetch;
    }
}
