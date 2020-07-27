<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\NodeChanger\NodeMissingIdentifierException;
use Rector\NodeNameResolver\NodeNameResolver;

/**
 * This class renames node identifier, e.g. ClassMethod rename:
 *
 * -public function someMethod()
 * +public function newMethod()
 */
final class IdentifierManipulator
{
    /**
     * @var string[]
     */
    private const NODE_CLASSES_WITH_IDENTIFIER = [
        ClassConstFetch::class, MethodCall::class, PropertyFetch::class, StaticCall::class, ClassMethod::class,
    ];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(Node $node, array $renameMethodMap): void
    {
        $this->ensureNodeHasIdentifier($node);

        $oldNodeMethodName = $this->resolveOldMethodName($node);
        if ($oldNodeMethodName === null) {
            return;
        }

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }

    /**
     * @param ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node
     */
    public function removeSuffix(Node $node, string $suffixToRemove): void
    {
        $this->ensureNodeHasIdentifier($node);

        $name = $this->nodeNameResolver->getName($node);
        if ($name === null) {
            return;
        }
        $newName = Strings::replace($name, sprintf('#%s$#', $suffixToRemove));

        $node->name = new Identifier($newName);
    }

    private function ensureNodeHasIdentifier(Node $node): void
    {
        if (in_array(get_class($node), self::NODE_CLASSES_WITH_IDENTIFIER, true)) {
            return;
        }

        throw new NodeMissingIdentifierException(sprintf(
            'Node "%s" does not contain a "$name" property with "%s". Pass only one of "%s".',
            get_class($node),
            Identifier::class,
            implode('", "', self::NODE_CLASSES_WITH_IDENTIFIER)
        ));
    }

    private function resolveOldMethodName(Node $node): ?string
    {
        if ($node instanceof StaticCall || $node instanceof MethodCall) {
            return $this->nodeNameResolver->getName($node->name);
        }

        return $this->nodeNameResolver->getName($node);
    }
}
