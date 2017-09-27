<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class MainNodeTraverser extends NodeTraverser
{
    /**
     * @var NodeVisitor[]
     */
    private $cachedNodeVisitors = [];

    /**
     * @param string[] $rectorClasses
     */
    public function enableOnlyRectorClasses(array $rectorClasses): void
    {
        $this->ensureNodeVisitorsAreCached();

        $this->visitors = [];

        foreach ($this->cachedNodeVisitors as $cachedNodeVisitor) {
            if ($this->isObjectOfTypes($cachedNodeVisitor, $rectorClasses)) {
                $this->addVisitor($cachedNodeVisitor);
            }
        }
    }

    private function ensureNodeVisitorsAreCached(): void
    {
        if ($this->cachedNodeVisitors === []) {
            $this->cachedNodeVisitors = $this->visitors;
        }
    }

    /**
     * @param string[] $types
     */
    private function isObjectOfTypes(NodeVisitor $nodeVisitor, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($nodeVisitor, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
