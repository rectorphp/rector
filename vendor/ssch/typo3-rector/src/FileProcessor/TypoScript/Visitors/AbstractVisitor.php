<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Visitors;

use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Rector\Core\Contract\Rector\RectorInterface;
abstract class AbstractVisitor implements \RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Traverser\Visitor, \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @var bool
     */
    protected $hasChanged = \false;
    public function enterTree(array $statements) : void
    {
    }
    public function enterNode(\RectorPrefix20210603\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
    }
    public function exitNode(\RectorPrefix20210603\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
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
