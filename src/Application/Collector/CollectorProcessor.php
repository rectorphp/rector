<?php

declare (strict_types=1);
namespace Rector\Core\Application\Collector;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPStan\Collectors\CollectedData;
use PHPStan\Collectors\Registry;
final class CollectorProcessor
{
    /**
     * @readonly
     * @var \PhpParser\NodeTraverser
     */
    private $nodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\Application\Collector\CollectorNodeVisitor
     */
    private $collectorNodeVisitor;
    public function __construct(Registry $collectorRegistry)
    {
        $nodeTraverser = new NodeTraverser();
        $this->collectorNodeVisitor = new \Rector\Core\Application\Collector\CollectorNodeVisitor($collectorRegistry);
        $nodeTraverser->addVisitor($this->collectorNodeVisitor);
        $this->nodeTraverser = $nodeTraverser;
    }
    /**
     * @param Node[] $stmts
     * @return CollectedData[]
     */
    public function process(array $stmts) : array
    {
        $this->nodeTraverser->traverse($stmts);
        return $this->collectorNodeVisitor->getCollectedData();
    }
}
