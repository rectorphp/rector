<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;

final class MethodNameChanger
{
    /**
     * @param string[] $renameMethodMap
     */
    public function renameNode(MethodCall $node, array $renameMethodMap): void
    {
        $oldNodeMethodName = $node->name->toString();

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }
}
