<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\ConfigArrayHandler;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\NodeFactory;
use Rector\Symfony\Utils\StringUtils;
final class NestedConfigCallsFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var array<string, string>
     */
    private const GROUPING_METHOD_NAME_TO_SPLIT = ['connections' => 'connection', 'entity_managers' => 'entityManager'];
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param mixed[] $values
     * @return array<Expression<MethodCall>>
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\MethodCall $configCaller
     */
    public function create(array $values, $configCaller, string $mainMethodName) : array
    {
        unset($values[0]);
        $methodCallStmts = [];
        foreach ($values as $value) {
            if (\is_array($value)) {
                // doctrine
                foreach (self::GROUPING_METHOD_NAME_TO_SPLIT as $groupingMethodName => $splitMethodName) {
                    if ($mainMethodName === $groupingMethodName) {
                        foreach ($value as $connectionName => $connectionConfiguration) {
                            $connectionArgs = $this->nodeFactory->createArgs([$connectionName]);
                            $connectionMethodCall = new MethodCall($configCaller, $splitMethodName, $connectionArgs);
                            foreach ($connectionConfiguration as $configurationMethod => $configurationValue) {
                                $configurationMethod = StringUtils::underscoreToCamelCase($configurationMethod);
                                $args = $this->nodeFactory->createArgs([$configurationValue]);
                                $connectionMethodCall = new MethodCall($connectionMethodCall, $configurationMethod, $args);
                            }
                            $methodCallStmts[] = new Expression($connectionMethodCall);
                        }
                        continue 2;
                    }
                }
                $mainMethodCall = new MethodCall($configCaller, $mainMethodName);
                foreach ($value as $methodName => $parameters) {
                    // security
                    if ($methodName === 'role') {
                        $methodName = 'roles';
                        $parameters = [$parameters];
                    }
                    $args = $this->nodeFactory->createArgs([$parameters]);
                    $mainMethodCall = new MethodCall($mainMethodCall, $methodName, $args);
                }
                $methodCallStmts[] = new Expression($mainMethodCall);
            }
        }
        return $methodCallStmts;
    }
}
