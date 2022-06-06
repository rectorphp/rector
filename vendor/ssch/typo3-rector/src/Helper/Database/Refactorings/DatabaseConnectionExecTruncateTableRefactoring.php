<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Database\Refactorings;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings\DatabaseConnectionToDbalRefactoring;
final class DatabaseConnectionExecTruncateTableRefactoring implements DatabaseConnectionToDbalRefactoring
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Database\Refactorings\ConnectionCallFactory
     */
    private $connectionCallFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(ConnectionCallFactory $connectionCallFactory, NodeFactory $nodeFactory)
    {
        $this->connectionCallFactory = $connectionCallFactory;
        $this->nodeFactory = $nodeFactory;
    }
    public function refactor(MethodCall $oldMethodCall) : array
    {
        $tableArgument = \array_shift($oldMethodCall->args);
        if (!$tableArgument instanceof Arg) {
            return [];
        }
        $connectionAssignment = $this->connectionCallFactory->createConnectionCall($tableArgument);
        $connectionInsertCall = $this->nodeFactory->createMethodCall(new Variable('connection'), 'truncate', [$tableArgument->value]);
        return [$connectionAssignment, $connectionInsertCall];
    }
    public function canHandle(string $methodName) : bool
    {
        return 'exec_TRUNCATEquery' === $methodName;
    }
}
