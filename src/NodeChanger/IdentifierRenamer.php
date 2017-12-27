<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;

final class IdentifierRenamer
{
    public function renameNode(Node $node, string $newMethodName): void
    {
        $this->ensureNodeHasIdentifier($node);

        $node->name = new Identifier($newMethodName);
    }

    /**
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(Node $node, array $renameMethodMap): void
    {
        $this->ensureNodeHasIdentifier($node);

        $oldNodeMethodName = $node->name->toString();

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }

    private function ensureNodeHasIdentifier(Node $node): void
    {
        if (! in_array(get_class($node), [
            ClassConstFetch::class, MethodCall::class, PropertyFetch::class, StaticCall::class,
        ])) {
            throw new Exception('The given Node does not have a valid Identifier.');
        }
    }
}
