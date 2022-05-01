<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Helmich\TypoScriptParser\Parser\Traverser;

use Helmich\TypoScriptParser\Parser\AST\Statement;
/**
 * Class AggregatingVisitor
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\Traverser
 */
class AggregatingVisitor implements \RectorPrefix20220501\Helmich\TypoScriptParser\Parser\Traverser\Visitor
{
    /** @var Visitor[] */
    private $visitors = [];
    /**
     * @param Visitor $visitor
     * @return void
     */
    public function addVisitor(\RectorPrefix20220501\Helmich\TypoScriptParser\Parser\Traverser\Visitor $visitor) : void
    {
        $this->visitors[\spl_object_hash($visitor)] = $visitor;
    }
    /**
     * @param Statement[] $statements
     * @return void
     */
    public function enterTree(array $statements) : void
    {
        foreach ($this->visitors as $visitor) {
            $visitor->enterTree($statements);
        }
    }
    /**
     * @param Statement $statement
     * @return void
     */
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        foreach ($this->visitors as $visitor) {
            $visitor->enterNode($statement);
        }
    }
    /**
     * @param Statement $statement
     * @return void
     */
    public function exitNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        foreach ($this->visitors as $visitor) {
            $visitor->exitNode($statement);
        }
    }
    /**
     * @param Statement[] $statements
     * @return void
     */
    public function exitTree(array $statements) : void
    {
        foreach ($this->visitors as $visitor) {
            $visitor->exitTree($statements);
        }
    }
}
