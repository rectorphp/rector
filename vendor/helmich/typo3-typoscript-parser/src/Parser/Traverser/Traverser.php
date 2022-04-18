<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Traverser;

use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
/**
 * Class Traverser
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\Traverser
 */
class Traverser
{
    /** @var Statement[] */
    private $statements;
    /** @var AggregatingVisitor */
    private $visitors;
    /**
     * @param Statement[] $statements
     */
    public function __construct(array $statements)
    {
        $this->statements = $statements;
        $this->visitors = new \RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Traverser\AggregatingVisitor();
    }
    /**
     * @param Visitor $visitor
     */
    public function addVisitor(\RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Traverser\Visitor $visitor) : void
    {
        $this->visitors->addVisitor($visitor);
    }
    /**
     * @return void
     */
    public function walk() : void
    {
        $this->visitors->enterTree($this->statements);
        $this->walkRecursive($this->statements);
        $this->visitors->exitTree($this->statements);
    }
    /**
     * @param Statement[] $statements
     * @return Statement[]
     */
    private function walkRecursive(array $statements) : array
    {
        foreach ($statements as $statement) {
            $this->visitors->enterNode($statement);
            if ($statement instanceof \RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\NestedAssignment) {
                $statement->statements = $this->walkRecursive($statement->statements);
            } elseif ($statement instanceof \RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement) {
                $statement->ifStatements = $this->walkRecursive($statement->ifStatements);
                $statement->elseStatements = $this->walkRecursive($statement->elseStatements);
            }
            $this->visitors->exitNode($statement);
        }
        return $statements;
    }
}
/**
 * Class Traverser
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\Traverser
 */
\class_alias('RectorPrefix20220418\\Helmich\\TypoScriptParser\\Parser\\Traverser\\Traverser', 'Helmich\\TypoScriptParser\\Parser\\Traverser\\Traverser', \false);
