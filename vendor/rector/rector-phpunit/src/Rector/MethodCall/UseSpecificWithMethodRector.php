<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/29685/files
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector\UseSpecificWillMethodRectorTest
 */
final class UseSpecificWithMethodRector extends AbstractRector
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
        return new RuleDefinition('Changes ->with() to more specific method', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->createMock('SomeClass');

        $translator->expects($this->any())
            ->method('trans')
            ->with($this->equalTo('old max {{ max }}!'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->createMock('SomeClass');

        $translator->expects($this->any())
            ->method('trans')
            ->with('old max {{ max }}!');
    }
}
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        // we cannot check caller types, as on old PHPUnit version, this the magic ->method() call result to a mixed type
        if (!$this->isName($node->name, 'with')) {
            return null;
        }
        foreach ($node->getArgs() as $i => $argNode) {
            if (!$argNode->value instanceof MethodCall) {
                continue;
            }
            $methodCall = $argNode->value;
            if (!$this->isName($methodCall->name, 'equalTo')) {
                continue;
            }
            $node->args[$i] = $methodCall->args[0];
        }
        return $node;
    }
}
