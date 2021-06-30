<?php

declare (strict_types=1);
namespace RectorPrefix20210630\Helmich\TypoScriptParser\Parser\Traverser;

use RectorPrefix20210630\Helmich\TypoScriptParser\Parser\AST\Statement;
/**
 * Interface Visitor
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\Traverser
 */
interface Visitor
{
    /**
     * @param Statement[] $statements
     * @return void
     */
    public function enterTree(array $statements) : void;
    /**
     * @param Statement $statement
     * @return void
     */
    public function enterNode(\RectorPrefix20210630\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void;
    /**
     * @param Statement $statement
     * @return void
     */
    public function exitNode(\RectorPrefix20210630\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void;
    /**
     * @param Statement[] $statements
     * @return void
     */
    public function exitTree(array $statements) : void;
}
