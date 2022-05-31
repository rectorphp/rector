<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
abstract class AbstractTypoScriptRector implements \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Traverser\Visitor, \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface
{
    /**
     * @var bool
     */
    protected $hasChanged = \false;
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
}
