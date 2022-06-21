<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
final class TerminatedNodeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const TERMINATED_NODES = [Return_::class, Throw_::class];
    /**
     * @param \PhpParser\Node\Stmt\TryCatch|\PhpParser\Node\Stmt\If_ $node
     */
    public function isAlwaysTerminated($node) : bool
    {
        if ($node instanceof TryCatch) {
            if ($node->finally instanceof Finally_ && $this->isTerminated($node->finally->stmts)) {
                return \true;
            }
            foreach ($node->catches as $catch) {
                if (!$this->isTerminated($catch->stmts)) {
                    return \false;
                }
            }
            return $this->isTerminated($node->stmts);
        }
        return $this->isTerminatedIf($node);
    }
    private function isTerminatedIf(If_ $if) : bool
    {
        // Without ElseIf_[] and Else_, after If_ is possibly executable
        if ($if->elseifs === [] && !$if->else instanceof Else_) {
            return \false;
        }
        foreach ($if->elseifs as $elseIf) {
            if (!$this->isTerminated($elseIf->stmts)) {
                return \false;
            }
        }
        if (!$this->isTerminated($if->stmts)) {
            return \false;
        }
        if (!$if->else instanceof Else_) {
            return \false;
        }
        return $this->isTerminated($if->else->stmts);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isTerminated(array $stmts) : bool
    {
        if ($stmts === []) {
            return \false;
        }
        \end($stmts);
        $lastKey = \key($stmts);
        $lastNode = $stmts[$lastKey];
        if ($lastNode instanceof Expression) {
            return $lastNode->expr instanceof Exit_;
        }
        return \in_array(\get_class($lastNode), self::TERMINATED_NODES, \true);
    }
}
