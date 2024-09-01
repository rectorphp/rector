<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\ConfigArrayHandler;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\NodeFactory;
use Rector\Symfony\Configs\Enum\GroupingMethods;
use Rector\Symfony\Configs\Enum\SecurityConfigKey;
use Rector\Symfony\Utils\StringUtils;
use RectorPrefix202409\Webmozart\Assert\Assert;
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
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\MethodCall $configCaller
     */
    public function create(array $values, $configCaller, string $mainMethodName) : array
    {
        $methodCallStmts = [];
        foreach ($values as $value) {
            if (\is_array($value)) {
                // doctrine
                foreach (GroupingMethods::GROUPING_METHOD_NAME_TO_SPLIT as $groupingMethodName => $splitMethodName) {
                    if ($mainMethodName === $groupingMethodName) {
                        // @possibly here
                        foreach ($value as $connectionName => $connectionConfiguration) {
                            $connectionArgs = $this->nodeFactory->createArgs([$connectionName]);
                            $connectionMethodCall = new MethodCall($configCaller, $splitMethodName, $connectionArgs);
                            $connectionMethodCall = $this->createMainMethodCall($connectionConfiguration, $connectionMethodCall);
                            $methodCallStmts[] = new Expression($connectionMethodCall);
                        }
                        continue 2;
                    }
                }
                $mainMethodCall = new MethodCall($configCaller, $mainMethodName);
                $mainMethodCall = $this->createMainMethodCall($value, $mainMethodCall);
                $methodCallStmts[] = new Expression($mainMethodCall);
            }
        }
        return $methodCallStmts;
    }
    /**
     * @param array<mixed, mixed> $value
     */
    private function createMainMethodCall(array $value, MethodCall $mainMethodCall) : MethodCall
    {
        foreach ($value as $methodName => $parameters) {
            // security
            if ($methodName === SecurityConfigKey::ROLE) {
                $methodName = SecurityConfigKey::ROLES;
                $parameters = [$parameters];
            } else {
                Assert::string($methodName);
                $methodName = StringUtils::underscoreToCamelCase($methodName);
            }
            if (isset(GroupingMethods::GROUPING_METHOD_NAME_TO_SPLIT[$methodName])) {
                $splitMethodName = GroupingMethods::GROUPING_METHOD_NAME_TO_SPLIT[$methodName];
                foreach ($parameters as $splitName => $splitParameters) {
                    $args = $this->nodeFactory->createArgs([$splitName]);
                    $mainMethodCall = new MethodCall($mainMethodCall, $splitMethodName, $args);
                    $mainMethodCall = $this->createMainMethodCall($splitParameters, $mainMethodCall);
                }
                continue;
            }
            // traverse nested arrays with recursion call
            $arrayIsListFunction = function (array $array) : bool {
                if (\function_exists('array_is_list')) {
                    return \array_is_list($array);
                }
                if ($array === []) {
                    return \true;
                }
                $current_key = 0;
                foreach ($array as $key => $noop) {
                    if ($key !== $current_key) {
                        return \false;
                    }
                    ++$current_key;
                }
                return \true;
            };
            // traverse nested arrays with recursion call
            if (\is_array($parameters) && !$arrayIsListFunction($parameters)) {
                $mainMethodCall = new MethodCall($mainMethodCall, $methodName);
                $mainMethodCall = $this->createMainMethodCall($parameters, $mainMethodCall);
                continue;
            }
            $args = $this->nodeFactory->createArgs([$parameters]);
            $mainMethodCall = new MethodCall($mainMethodCall, $methodName, $args);
        }
        return $mainMethodCall;
    }
}
