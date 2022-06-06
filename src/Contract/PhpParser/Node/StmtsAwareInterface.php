<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\PhpParser\Node;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt;
/**
 * @property Stmt[]|null $stmts
 */
interface StmtsAwareInterface extends Node
{
}
