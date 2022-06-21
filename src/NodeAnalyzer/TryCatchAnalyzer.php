<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
final class TryCatchAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const TERMINATED_NODES = [Return_::class, Throw_::class];
    public function isAlwaysTerminated(TryCatch $tryCatch) : bool
    {
        if ($tryCatch->finally instanceof Finally_ && $this->isTerminated($tryCatch->finally->stmts)) {
            return \true;
        }
        foreach ($tryCatch->catches as $catch) {
            if (!$this->isTerminated($catch->stmts)) {
                return \false;
            }
        }
        return $this->isTerminated($tryCatch->stmts);
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
