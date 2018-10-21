<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\NodeChanger\NodeMissingIdentifierException;
use Rector\NodeAnalyzer\NameResolver;
use function Safe\sprintf;

/**
 * This class renames node identifier, e.g. ClassMethod rename:
 *
 * -public function someMethod()
 * +public function newMethod()
 */
final class IdentifierRenamer
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var string[]
     */
    private $nodeClassesWithIdentifier = [
        ClassConstFetch::class, MethodCall::class, PropertyFetch::class, StaticCall::class, ClassMethod::class,
    ];

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function renameNode(Node $node, string $newMethodName): void
    {
        $this->ensureNodeHasIdentifier($node);

        /** @var ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node */
        $node->name = new Identifier($newMethodName);
    }

    /**
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(Node $node, array $renameMethodMap): void
    {
        $this->ensureNodeHasIdentifier($node);

        $oldNodeMethodName = $this->nameResolver->resolve($node);
        if (! $oldNodeMethodName) {
            return;
        }

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }

    public function removeSuffix(Node $node, string $suffixToRemove): void
    {
        $this->ensureNodeHasIdentifier($node);

        /** @var ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node */
        $node->name = new Identifier(preg_replace(sprintf('/%s$/', $suffixToRemove), '', $node->name));
    }

    private function ensureNodeHasIdentifier(Node $node): void
    {
        if (in_array(get_class($node), $this->nodeClassesWithIdentifier, true)) {
            return;
        }

        throw new NodeMissingIdentifierException(sprintf(
            'Node "%s" does not contain a "$name" property with "%s". Pass only one of "%s".',
            get_class($node),
            Identifier::class,
            implode('", "', $this->nodeClassesWithIdentifier)
        ));
    }
}
