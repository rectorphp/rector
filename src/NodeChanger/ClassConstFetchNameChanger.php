<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\ClassConstFetch;

final class ClassConstFetchNameChanger
{
    public function renameNode(ClassConstFetch $node, string $newMethodName): void
    {
        $node->name = new Identifier($newMethodName);
    }

    /**
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(ClassConstFetch $node, array $renameMethodMap): void
    {
        $oldNodeMethodName = $node->name->toString();

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }
}
