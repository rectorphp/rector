<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ReadWrite\ParentNodeReadAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
use RectorPrefix20220606\Rector\ReadWrite\Guard\VariableToConstantGuard;
final class ArgParentNodeReadAnalyzer implements ParentNodeReadAnalyzerInterface
{
    /**
     * @readonly
     * @var \Rector\ReadWrite\Guard\VariableToConstantGuard
     */
    private $variableToConstantGuard;
    public function __construct(VariableToConstantGuard $variableToConstantGuard)
    {
        $this->variableToConstantGuard = $variableToConstantGuard;
    }
    public function isRead(Expr $expr, Node $parentNode) : bool
    {
        if (!$parentNode instanceof Arg) {
            return \false;
        }
        return $this->variableToConstantGuard->isReadArg($parentNode);
    }
}
