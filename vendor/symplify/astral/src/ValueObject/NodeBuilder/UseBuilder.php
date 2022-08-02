<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\ValueObject\NodeBuilder;

use PhpParser\Builder\Use_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_ as UseStmt;
/**
 * @api
 * Fixed duplicated naming in php-parser and prevents confusion
 */
final class UseBuilder extends Use_
{
    /**
     * @param \PhpParser\Node\Name|string $name
     */
    public function __construct($name, int $type = UseStmt::TYPE_NORMAL)
    {
        parent::__construct($name, $type);
    }
}
