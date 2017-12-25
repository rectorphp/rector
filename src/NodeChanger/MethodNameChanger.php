<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;

final class MethodNameChanger
{

    public function renameNode(MethodCall $node, $oldToNewMethods): void
    {
        if (is_array($oldToNewMethods)) {
            $oldNodeMethodName = $node->name->toString();

            $node->name = new Identifier($oldToNewMethods[$oldNodeMethodName]);
        } elseif (is_string($oldToNewMethods)) {
            $node->name = new Identifier($oldToNewMethods);
        }
    }
}
