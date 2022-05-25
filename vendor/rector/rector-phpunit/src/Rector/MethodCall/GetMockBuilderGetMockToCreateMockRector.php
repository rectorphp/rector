<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/lmc-eu/steward/pull/187/files#diff-c7e8c65e59b8b4ff8b54325814d4ba55L80
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\GetMockBuilderGetMockToCreateMockRectorTest
 */
final class GetMockBuilderGetMockToCreateMockRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const USELESS_METHOD_NAMES = ['disableOriginalConstructor', 'onlyMethods', 'setMethods', 'setMethodsExcept'];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove getMockBuilder() to createMock()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node->name, 'getMock')) {
            return null;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        // traverse up over useless methods until we reach the top one
        $currentMethodCall = $node->var;
        while ($currentMethodCall instanceof \PhpParser\Node\Expr\MethodCall && $this->isNames($currentMethodCall->name, self::USELESS_METHOD_NAMES)) {
            $currentMethodCall = $currentMethodCall->var;
        }
        if (!$currentMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->isName($currentMethodCall->name, 'getMockBuilder')) {
            return null;
        }
        $args = $currentMethodCall->args;
        $thisVariable = $currentMethodCall->var;
        return new \PhpParser\Node\Expr\MethodCall($thisVariable, 'createMock', $args);
    }
}
