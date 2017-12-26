<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;

final class StaticCallNameChanger
{
    public function renameNode(StaticCall $node, string $newMethodName): void
    {
        $node->name = new Identifier($newMethodName);
    }

    /**
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(StaticCall $node, array $renameMethodMap): void
    {
        $oldNodeMethodName = $node->name->toString();

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }
}
