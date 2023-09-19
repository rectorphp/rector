<?php

declare (strict_types=1);
namespace Rector\Core\Application\Collector;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\CollectedData;
use PHPStan\Collectors\Registry;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Throwable;
/**
 * @see Mimics https://github.com/phpstan/phpstan-src/commit/bccd1cd58e0d117ac8755ab228e338d966cac25a
 */
final class CollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @readonly
     * @var \PHPStan\Collectors\Registry
     */
    private $collectorRegistry;
    /**
     * @var CollectedData[]
     */
    private $collectedDatas = [];
    public function __construct(Registry $collectorRegistry)
    {
        $this->collectorRegistry = $collectorRegistry;
    }
    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->collectedDatas = [];
        return null;
    }
    public function enterNode(Node $node)
    {
        $collectors = $this->collectorRegistry->getCollectors(\get_class($node));
        /** @var Scope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        foreach ($collectors as $collector) {
            try {
                $collectedData = $collector->processNode($node, $scope);
            } catch (Throwable $exception) {
                // nothing to collect
                continue;
            }
            // no data collected
            if ($collectedData === null) {
                continue;
            }
            $this->collectedDatas[] = new CollectedData($collectedData, $scope->getFile(), \get_class($collector));
        }
        return null;
    }
    /**
     * @return CollectedData[]
     */
    public function getCollectedData() : array
    {
        return $this->collectedDatas;
    }
}
