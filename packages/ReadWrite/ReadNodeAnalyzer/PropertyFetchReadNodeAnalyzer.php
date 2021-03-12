<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;

final class PropertyFetchReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    /**
     * @var ReadExprAnalyzer
     */
    private $readExprAnalyzer;

    /**
     * @var NodeUsageFinder
     */
    private $nodeUsageFinder;

    public function __construct(ReadExprAnalyzer $readExprAnalyzer, NodeUsageFinder $nodeUsageFinder)
    {
        $this->readExprAnalyzer = $readExprAnalyzer;
        $this->nodeUsageFinder = $nodeUsageFinder;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof PropertyFetch;
    }

    /**
     * @param PropertyFetch $node
     */
    public function isRead(Node $node): bool
    {
        $propertyFetchUsages = $this->nodeUsageFinder->findPropertyFetchUsages($node);
        foreach ($propertyFetchUsages as $propertyFetchUsage) {
            if ($this->readExprAnalyzer->isReadContext($propertyFetchUsage)) {
                return true;
            }
        }

        return false;
    }
}
