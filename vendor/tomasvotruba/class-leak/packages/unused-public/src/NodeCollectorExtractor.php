<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic;

use PHPStan\Node\CollectedDataNode;
use RectorPrefix202608\TomasVotruba\UnusedPublic\CollectorMapper\MethodCallCollectorMapper;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\Callable_\AttributeCallableCollector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\Callable_\CallableTypeCollector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\MethodCall\MethodCallableCollector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\MethodCall\MethodCallCollector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\StaticCall\StaticMethodCallableCollector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\StaticCall\StaticMethodCallCollector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ValueObject\LocalAndExternalMethodCallReferences;
final class NodeCollectorExtractor
{
    /**
     * @readonly
     */
    private MethodCallCollectorMapper $methodCallCollectorMapper;
    public function __construct(MethodCallCollectorMapper $methodCallCollectorMapper)
    {
        $this->methodCallCollectorMapper = $methodCallCollectorMapper;
    }
    public function extractLocalAndExternalMethodCallReferences(CollectedDataNode $collectedDataNode): LocalAndExternalMethodCallReferences
    {
        $collectedDatas = $this->extractCollectedDatas($collectedDataNode);
        return $this->methodCallCollectorMapper->mapToLocalAndExternal($collectedDatas);
    }
    /**
     * @return string[]
     */
    public function extractMethodCallReferences(CollectedDataNode $collectedDataNode): array
    {
        $collectedDatas = $this->extractCollectedDatas($collectedDataNode);
        return $this->methodCallCollectorMapper->mapToMethodCallReferences($collectedDatas);
    }
    /**
     * @return array<int, array<string, list<(non-empty-array<string>|null)>>>
     */
    private function extractCollectedDatas(CollectedDataNode $collectedDataNode): array
    {
        return [$collectedDataNode->get(MethodCallCollector::class), $collectedDataNode->get(MethodCallableCollector::class), $collectedDataNode->get(StaticMethodCallCollector::class), $collectedDataNode->get(StaticMethodCallableCollector::class), $collectedDataNode->get(AttributeCallableCollector::class), $collectedDataNode->get(CallableTypeCollector::class)];
    }
}
