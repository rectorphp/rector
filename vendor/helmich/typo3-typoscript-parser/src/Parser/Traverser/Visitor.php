<?php

declare (strict_types=1);
namespace RectorPrefix20210804\Helmich\TypoScriptParser\Parser\Traverser;

use RectorPrefix20210804\Helmich\TypoScriptParser\Parser\AST\Statement;
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
    public function enterTree($statements) : void;
    /**
     * @param Statement $statement
     * @return void
     */
    public function enterNode($statement) : void;
    /**
     * @param Statement $statement
     * @return void
     */
    public function exitNode($statement) : void;
    /**
     * @param Statement[] $statements
     * @return void
     */
    public function exitTree($statements) : void;
}
