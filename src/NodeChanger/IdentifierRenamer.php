<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use Exception;
use Nette\Utils\Strings;
use PhpParser\Node;
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
        $typeNode = Strings::after(get_class($node), '\\', 3);

        if (! in_array($typeNode, [
            'MethodCall', 'StaticCall', 'PropertyFetch', 'ClassConstFetch',
        ])) {
            throw new Exception('The given Node does not have a valid Identifier.');
        }
    }
}
