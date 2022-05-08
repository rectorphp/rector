<?php

declare (strict_types=1);
namespace PhpParser\Node\Stmt;

use PhpParser\Node;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
class TryCatch extends \PhpParser\Node\Stmt implements \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface
{
    /** @var Node\Stmt[] Statements */
    public $stmts;
    /** @var Catch_[] Catches */
    public $catches;
    /** @var null|Finally_ Optional finally node */
    public $finally;
    /**
     * Constructs a try catch node.
     *
     * @param Node\Stmt[]   $stmts      Statements
     * @param Catch_[]      $catches    Catches
     * @param null|Finally_ $finally    Optional finally node
     * @param array         $attributes Additional attributes
     */
    public function __construct(array $stmts, array $catches, \PhpParser\Node\Stmt\Finally_ $finally = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->stmts = $stmts;
        $this->catches = $catches;
        $this->finally = $finally;
    }
    public function getSubNodeNames() : array
    {
        return ['stmts', 'catches', 'finally'];
    }
    public function getType() : string
    {
        return 'Stmt_TryCatch';
    }
}
