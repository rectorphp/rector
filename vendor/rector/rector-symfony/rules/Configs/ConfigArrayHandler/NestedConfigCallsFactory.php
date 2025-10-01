<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\ConfigArrayHandler;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\NodeFactory;
use Rector\Symfony\Configs\Enum\GroupingMethods;
use Rector\Symfony\Configs\Enum\SecurityConfigKey;
use Rector\Symfony\Utils\StringUtils;
use RectorPrefix202510\Webmozart\Assert\Assert;
final class NestedConfigCallsFactory
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @var array<string, string>
     */
    private const METHOD_RENAMES = [
        // monolog
        'excludedHttpCodes' => 'excludedHttpCode',
        // doctrine
        'result_cache_driver' => 'resultCacheDriver',
        'query_cache_driver' => 'queryCacheDriver',
    ];
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param mixed[] $values
     * @return array<Expression<MethodCall>>
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\MethodCall $configCaller
     * @param string|int|null $nextKey
     */
    public function create(array $values, $configCaller, string $mainMethodName, bool $nextKeyArgument, $nextKey = null): array
    {
        $methodCallStmts = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                // symfony framework cache
                if ($mainMethodName === 'cache') {
                    $configCaller = new MethodCall($configCaller, 'cache', []);
                    foreach ($value as $subKey => $subValue) {
                        if ($subKey === 'pools') {
                            foreach ($subValue as $poolName => $poolConfiguration) {
                                $poolMethodCall = new MethodCall($configCaller, 'pool', [new Arg(new String_($poolName))]);
                                foreach ($poolConfiguration as $poolMethodName => $poolParameters) {
                                    Assert::string($poolMethodName);
                                    $poolMethodCall = new MethodCall($poolMethodCall, $poolMethodName, $this->nodeFactory->createArgs([$poolParameters]));
                                }
                                $methodCallStmts[] = new Expression($poolMethodCall);
                            }
                        }
                    }
                    continue;
                }
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
                $mainMethodName = self::METHOD_RENAMES[$mainMethodName] ?? $mainMethodName;
                $mainMethodCall = new MethodCall($configCaller, $mainMethodName);
                if ($key === 0 && $nextKeyArgument && is_string($nextKey)) {
                    $mainMethodCall->args[] = new Arg(new String_($nextKey));
                }
                $mainMethodCall = $this->createMainMethodCall($value, $mainMethodCall);
                $methodCallStmts[] = new Expression($mainMethodCall);
            }
        }
        return $methodCallStmts;
    }
    /**
     * @param array<mixed, mixed> $value
     */
    private function createMainMethodCall(array $value, MethodCall $mainMethodCall): MethodCall
    {
        foreach ($value as $methodName => $parameters) {
            Assert::string($methodName);
            // security
            if ($methodName === SecurityConfigKey::ROLE) {
                $methodName = SecurityConfigKey::ROLES;
                $parameters = [$parameters];
            } else {
                $methodName = StringUtils::underscoreToCamelCase($methodName);
            }
            $methodName = self::METHOD_RENAMES[$methodName] ?? $methodName;
            // special case for dbal()->connection()
            if ($methodName === 'mappingTypes') {
                foreach ($parameters as $name => $type) {
                    $args = $this->nodeFactory->createArgs([$name, $type]);
                    $mainMethodCall = new MethodCall($mainMethodCall, 'mappingType', $args);
                }
                continue;
            } elseif (isset(GroupingMethods::GROUPING_METHOD_NAME_TO_SPLIT[$methodName])) {
                $splitMethodName = GroupingMethods::GROUPING_METHOD_NAME_TO_SPLIT[$methodName];
                foreach ($parameters as $splitName => $splitParameters) {
                    $args = $this->nodeFactory->createArgs([$splitName]);
                    $mainMethodCall = new MethodCall($mainMethodCall, $splitMethodName, $args);
                    $mainMethodCall = $this->createMainMethodCall($splitParameters, $mainMethodCall);
                }
                continue;
            }
            // traverse nested arrays with recursion call
            $arrayIsListFunction = function (array $array): bool {
                if (function_exists('array_is_list')) {
                    return array_is_list($array);
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
            if (is_array($parameters) && !$arrayIsListFunction($parameters)) {
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
