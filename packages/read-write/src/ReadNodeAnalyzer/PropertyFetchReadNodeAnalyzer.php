<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;

final class PropertyFetchReadNodeAnalyzer extends AbstractReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
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
            if ($this->isCurrentContextRead($propertyFetchUsage)) {
                return true;
            }
        }

        return false;
    }
}
