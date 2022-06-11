<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/lmc-eu/steward/pull/187/files#diff-c7e8c65e59b8b4ff8b54325814d4ba55L80
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\GetMockBuilderGetMockToCreateMockRectorTest
 */
final class GetMockBuilderGetMockToCreateMockRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const USELESS_METHOD_NAMES = ['disableOriginalConstructor', 'onlyMethods', 'setMethods', 'setMethodsExcept'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove getMockBuilder() to createMock()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SomeClass')
           ->disableOriginalConstructor()
           ->getMock();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SomeClass');
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'getMock')) {
            return null;
        }
        if (!$node->var instanceof MethodCall) {
            return null;
        }
        // traverse up over useless methods until we reach the top one
        $currentMethodCall = $node->var;
        while ($currentMethodCall instanceof MethodCall && $this->isNames($currentMethodCall->name, self::USELESS_METHOD_NAMES)) {
            $currentMethodCall = $currentMethodCall->var;
        }
        if (!$currentMethodCall instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($currentMethodCall->name, 'getMockBuilder')) {
            return null;
        }
        $args = $currentMethodCall->args;
        $thisVariable = $currentMethodCall->var;
        return new MethodCall($thisVariable, 'createMock', $args);
    }
}
