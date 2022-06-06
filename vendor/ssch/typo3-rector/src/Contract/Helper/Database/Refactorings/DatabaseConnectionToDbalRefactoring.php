<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
interface DatabaseConnectionToDbalRefactoring
{
    /**
     * @return Expr[]
     */
    public function refactor(MethodCall $oldMethodCall) : array;
    public function canHandle(string $methodName) : bool;
}
