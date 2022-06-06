<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\PhpParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt;
/**
 * This contract allows to autowire custom printer implementation
 */
interface NodePrinterInterface
{
    /**
     * @param \PhpParser\Node|mixed[]|null $node
     */
    public function print($node) : string;
    /**
     * @param Stmt[] $stmts
     */
    public function prettyPrint(array $stmts) : string;
    /**
     * @param Stmt[] $stmts
     */
    public function prettyPrintFile(array $stmts) : string;
}
