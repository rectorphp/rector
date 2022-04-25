<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
interface DatabaseConnectionToDbalRefactoring
{
    /**
     * @return Expr[]
     */
    public function refactor(\PhpParser\Node\Expr\MethodCall $oldMethodCall) : array;
    public function canHandle(string $methodName) : bool;
}
