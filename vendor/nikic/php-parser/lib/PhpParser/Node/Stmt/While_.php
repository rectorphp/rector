<?php

declare (strict_types=1);
namespace PhpParser\Node\Stmt;

use PhpParser\Node;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
class While_ extends \PhpParser\Node\Stmt implements \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface
{
    /** @var Node\Expr Condition */
    public $cond;
    /** @var Node\Stmt[] Statements */
    public $stmts;
    /**
     * Constructs a while node.
     *
     * @param Node\Expr   $cond       Condition
     * @param Node\Stmt[] $stmts      Statements
     * @param array       $attributes Additional attributes
     */
    public function __construct(\PhpParser\Node\Expr $cond, array $stmts = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->cond = $cond;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array
    {
        return ['cond', 'stmts'];
    }
    public function getType() : string
    {
        return 'Stmt_While';
    }
}
