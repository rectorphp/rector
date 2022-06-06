<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Stmt;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
class Else_ extends Node\Stmt implements StmtsAwareInterface
{
    /** @var Node\Stmt[] Statements */
    public $stmts;
    /**
     * Constructs an else node.
     *
     * @param Node\Stmt[] $stmts      Statements
     * @param array       $attributes Additional attributes
     */
    public function __construct(array $stmts = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array
    {
        return ['stmts'];
    }
    public function getType() : string
    {
        return 'Stmt_Else';
    }
}
