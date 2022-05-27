<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ParentNodeReadAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
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
