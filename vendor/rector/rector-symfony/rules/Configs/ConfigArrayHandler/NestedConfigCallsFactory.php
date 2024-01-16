<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\ConfigArrayHandler;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\NodeFactory;
final class NestedConfigCallsFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param mixed[] $values
     * @return array<Expression<MethodCall>>
     */
    public function create(array $values, Variable $configVariable, string $mainMethodName) : array
    {
        unset($values[0]);
        $methodCallStmts = [];
        foreach ($values as $value) {
            // build accessControl() method call here
            $accessControlMethodCall = new MethodCall($configVariable, $mainMethodName);
            foreach ($value as $methodName => $parameters) {
                // method correction
                if ($methodName === 'role') {
                    $methodName = 'roles';
                    $parameters = [$parameters];
                }
                $args = $this->nodeFactory->createArgs([$parameters]);
                $accessControlMethodCall = new MethodCall($accessControlMethodCall, $methodName, $args);
            }
            $methodCallStmts[] = new Expression($accessControlMethodCall);
        }
        return $methodCallStmts;
    }
}
