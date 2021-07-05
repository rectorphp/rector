<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
interface DatabaseConnectionToDbalRefactoring
{
    /**
     * @return Expr[]
     * @param \PhpParser\Node\Expr\MethodCall $oldNode
     */
    public function refactor($oldNode) : array;
    /**
     * @param string $methodName
     */
    public function canHandle($methodName) : bool;
}
