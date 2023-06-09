<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
final class StmtAndExpr
{
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_
     */
    private $stmt;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    /**
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    public function __construct($stmt, Expr $expr)
    {
        $this->stmt = $stmt;
        $this->expr = $expr;
    }
    /**
     * @return \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_
     */
    public function getStmt()
    {
        return $this->stmt;
    }
    public function getExpr() : Expr
    {
        return $this->expr;
    }
}
