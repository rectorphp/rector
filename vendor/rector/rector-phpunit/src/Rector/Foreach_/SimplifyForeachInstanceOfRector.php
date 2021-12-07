<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\NodeManipulator\ForeachManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\Foreach_\SimplifyForeachInstanceOfRector\SimplifyForeachInstanceOfRectorTest
 */
final class SimplifyForeachInstanceOfRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ForeachManipulator
     */
    private $foreachManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ForeachManipulator $foreachManipulator)
    {
        $this->foreachManipulator = $foreachManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify unnecessary foreach check of instances', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
foreach ($foos as $foo) {
    $this->assertInstanceOf(SplFileInfo::class, $foo);
}
CODE_SAMPLE
, '$this->assertContainsOnlyInstancesOf(\\SplFileInfo::class, $foos);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var MethodCall|StaticCall|null $matchedNode */
        $matchedNode = $this->foreachManipulator->matchOnlyStmt($node, function (\PhpParser\Node $node, \PhpParser\Node\Stmt\Foreach_ $foreachNode) : ?Node {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall && !$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return null;
            }
            if (!$this->isName($node->name, 'assertInstanceOf')) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($foreachNode->valueVar, $node->args[1]->value)) {
                return null;
            }
            return $node;
        });
        if ($matchedNode === null) {
            return null;
        }
        $args = [$matchedNode->args[0], new \PhpParser\Node\Arg($node->expr)];
        if ($matchedNode instanceof \PhpParser\Node\Expr\StaticCall) {
            return new \PhpParser\Node\Expr\StaticCall($matchedNode->class, 'assertContainsOnlyInstancesOf', $args);
        }
        return new \PhpParser\Node\Expr\MethodCall($matchedNode->var, 'assertContainsOnlyInstancesOf', $args);
    }
}
