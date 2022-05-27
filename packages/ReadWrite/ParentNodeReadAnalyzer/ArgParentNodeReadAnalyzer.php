<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ParentNodeReadAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
final class ArgParentNodeReadAnalyzer implements \Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface
{
    /**
     * @readonly
     * @var \Rector\ReadWrite\Guard\VariableToConstantGuard
     */
    private $variableToConstantGuard;
    public function __construct(\Rector\ReadWrite\Guard\VariableToConstantGuard $variableToConstantGuard)
    {
        $this->variableToConstantGuard = $variableToConstantGuard;
    }
    public function isRead(\PhpParser\Node\Expr $expr, \PhpParser\Node $parentNode) : bool
    {
        if (!$parentNode instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        return $this->variableToConstantGuard->isReadArg($parentNode);
    }
}
