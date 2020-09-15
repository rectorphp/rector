<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\PhpParser\Node\Manipulator\StmtsManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://engineering.helpscout.com/reducing-complexity-with-guard-clauses-in-php-and-javascript-74600fd865c7
 *
 * @see \Rector\SOLID\Tests\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector\ChangeIfElseValueAssignToEarlyReturnRectorTest
 */
final class ChangeIfElseValueAssignToEarlyReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var StmtsManipulator
     */
    private $stmtsManipulator;

    public function __construct(IfManipulator $ifManipulator, StmtsManipulator $stmtsManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->stmtsManipulator = $stmtsManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change if/else value to early return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            $docToken = $tokens[$this->getDocBlockIndex($tokens, $index)];
        } else {
            $docToken = null;
        }

        return $docToken;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            return $tokens[$this->getDocBlockIndex($tokens, $index)];
        }
        return null;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        if ($nextNode->expr === null) {
            return null;
        }

        if (! $this->ifManipulator->isIfAndElseWithSameVariableAssignAsLastStmts($node, $nextNode->expr)) {
            return null;
        }

        $lastIfStmtKey = array_key_last($node->stmts);

        /** @var Assign $assign */
        $assign = $this->stmtsManipulator->getUnwrappedLastStmt($node->stmts);

        $node->stmts[$lastIfStmtKey] = new Return_($assign->expr);

        /** @var Assign $assign */
        $assign = $this->stmtsManipulator->getUnwrappedLastStmt($node->else->stmts);

        $lastElseStmtKey = array_key_last($node->else->stmts);

        $elseStmts = $node->else->stmts;
        $elseStmts[$lastElseStmtKey] = new Return_($assign->expr);

        $node->else = null;
        $this->addNodesAfterNode($elseStmts, $node);

        $this->removeNode($nextNode);

        return $node;
    }
}
