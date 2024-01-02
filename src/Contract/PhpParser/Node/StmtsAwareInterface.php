<?php

declare (strict_types=1);
namespace Rector\Contract\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Stmt;
/**
 * @property Stmt[]|null $stmts
 */
interface StmtsAwareInterface extends Node
{
}
