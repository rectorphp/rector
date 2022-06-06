<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Stmt;

use RectorPrefix20220606\PhpParser\Node;
abstract class TraitUseAdaptation extends Node\Stmt
{
    /** @var Node\Name|null Trait name */
    public $trait;
    /** @var Node\Identifier Method name */
    public $method;
}
