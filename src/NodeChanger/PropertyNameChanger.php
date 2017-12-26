<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;

final class PropertyNameChanger
{
    public function renameNode(PropertyFetch $node, string $newMethodName): void
    {
        $node->name = new Identifier($newMethodName);
    }

    /**
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(PropertyFetch $node, array $renameMethodMap): void
    {
        $oldNodeMethodName = $node->name->toString();

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }
}
