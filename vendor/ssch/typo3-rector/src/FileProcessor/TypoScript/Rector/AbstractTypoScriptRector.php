<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20211231\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
abstract class AbstractTypoScriptRector implements \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\Traverser\Visitor, \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface
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
    public function enterTree(array $statements) : void
    {
    }
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
    }
    public function exitNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
    }
    public function exitTree(array $statements) : void
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
