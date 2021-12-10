<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20211210\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
abstract class AbstractTypoScriptRector implements \RectorPrefix20211210\Helmich\TypoScriptParser\Parser\Traverser\Visitor, \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface
{
    /**
     * @var bool
     */
    protected $hasChanged = \false;
    /**
     * @var \Helmich\TypoScriptParser\Parser\AST\Statement|null
     */
    protected $originalStatement;
    /**
     * @var \Helmich\TypoScriptParser\Parser\AST\Statement|null
     */
    protected $newStatement;
    /**
     * @param mixed[] $statements
     */
    public function enterTree($statements) : void
    {
    }
    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement $statement
     */
    public function enterNode($statement) : void
    {
    }
    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement $statement
     */
    public function exitNode($statement) : void
    {
    }
    /**
     * @param mixed[] $statements
     */
    public function exitTree($statements) : void
    {
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
    public function getOriginalStatement() : ?\Helmich\TypoScriptParser\Parser\AST\Statement
    {
        return $this->originalStatement;
    }
    public function getNewStatement() : ?\Helmich\TypoScriptParser\Parser\AST\Statement
    {
        return $this->newStatement;
    }
    public function reset() : void
    {
        $this->newStatement = null;
        $this->originalStatement = null;
    }
}
