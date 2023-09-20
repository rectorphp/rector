<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector\SimplifyIfNotNullReturnRectorTest
 */
final class SimplifyIfNotNullReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(IfManipulator $ifManipulator, ValueResolver $valueResolver)
    {
        $this->ifManipulator = $ifManipulator;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes redundant null check to instant return', [new CodeSample(<<<'CODE_SAMPLE'
$newNode = 'something';
if ($newNode !== null) {
    return $newNode;
}

return null;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$newNode = 'something';
return $newNode;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            if (!isset($node->stmts[$key + 1])) {
                return null;
            }
            $nextNode = $node->stmts[$key + 1];
            if (!$nextNode instanceof Return_) {
                continue;
            }
            $expr = $this->ifManipulator->matchIfNotNullReturnValue($stmt);
            if (!$expr instanceof Expr) {
                continue;
            }
            $insideIfNode = $stmt->stmts[0];
            if (!$nextNode->expr instanceof Expr) {
                continue;
            }
            if (!$this->valueResolver->isNull($nextNode->expr)) {
                continue;
            }
            unset($node->stmts[$key]);
            $node->stmts[$key + 1] = $insideIfNode;
            return $node;
        }
        return null;
    }
}
