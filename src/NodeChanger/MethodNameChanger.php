<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node;
use PhpParser\Node\Identifier;

final class MethodNameChanger
{
    /**
     * @param string|string[] $newMethodName
     */
    public function renameNode(Node $node, $newMethodName): void
    {
        if (is_array($newMethodName)) {
            $oldNodeMethodName = $node->name->toString();

            $node->name = new Identifier($newMethodName[$oldNodeMethodName]);
        } elseif (is_string($newMethodName)) {
            $node->name = new Identifier($newMethodName);
        }
    }
}
