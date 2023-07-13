<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachInstanceOfRector\SimplifyForeachInstanceOfRectorTest
 */
final class SimplifyForeachInstanceOfRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify unnecessary foreach check of instances', [new CodeSample(<<<'CODE_SAMPLE'
foreach ($foos as $foo) {
    $this->assertInstanceOf(SplFileInfo::class, $foo);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (\count($node->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $node->stmts[0];
        if (!$onlyStmt instanceof Expression) {
            return null;
        }
        $expr = $onlyStmt->expr;
        if (!$expr instanceof MethodCall && !$expr instanceof StaticCall) {
            return null;
        }
        if (!$this->isName($expr->name, 'assertInstanceOf')) {
            return null;
        }
        if ($expr->isFirstClassCallable()) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->valueVar, $expr->getArgs()[1]->value)) {
            return null;
        }
        // skip if there is a custom message included; it might be per item
        if (\count($expr->getArgs()) === 3) {
            return null;
        }
        $newArgs = [$expr->getArgs()[0], new Arg($node->expr)];
        if ($expr instanceof StaticCall) {
            $staticCall = new StaticCall($expr->class, 'assertContainsOnlyInstancesOf', $newArgs);
            return new Expression($staticCall);
        }
        $methodCall = new MethodCall($expr->var, 'assertContainsOnlyInstancesOf', $newArgs);
        return new Expression($methodCall);
    }
}
