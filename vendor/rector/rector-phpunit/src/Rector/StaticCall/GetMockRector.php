<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/5.7.0/src/Framework/TestCase.php#L1623
 * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L1452
 *
 * @see \Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\GetMockRectorTest
 */
final class GetMockRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns getMock*() methods to createMock()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->getMock("Class");', '$this->createMock("Class");'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->getMockWithoutInvokingTheOriginalConstructor("Class");', '$this->createMock("Class");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['getMock', 'getMockWithoutInvokingTheOriginalConstructor'])) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && $node->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        // narrow args to one
        if (\count($node->args) > 1) {
            $node->args = [$node->args[0]];
        }
        $node->name = new \PhpParser\Node\Identifier('createMock');
        return $node;
    }
}
