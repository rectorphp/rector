<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class ShortNameResolver
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var string[][]
     */
    private $shortNamesByNamespaceObjectHash = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        ClassNaming $classNaming
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->classNaming = $classNaming;
    }

    /**
     * @return string[]
     */
    public function resolveForNode(Node $node): array
    {
        /** @var Namespace_|null $namespace */
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace === null) {
            // only namespaced classes are supported
            return [];
        }

        // must be hash → unique per file
        $namespaceHash = spl_object_hash($namespace);
        if (isset($this->shortNamesByNamespaceObjectHash[$namespaceHash])) {
            return $this->shortNamesByNamespaceObjectHash[$namespaceHash];
        }

        $shortNames = $this->resolveForNamespace($namespace);
        $this->shortNamesByNamespaceObjectHash[$namespaceHash] = $shortNames;

        return $shortNames;
    }

    /**
     * Collects all "class <SomeClass>", "trait <SomeTrait>" and "interface <SomeInterface>"
     * @return string[]
     */
    public function resolveShortClassLikeNamesForNode(Node $node): array
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace === null) {
            // only handle namespace nodes
            return [];
        }

        $shortClassLikeNames = [];
        $this->callableNodeTraverser->traverseNodesWithCallable($namespace, function (Node $node) use (
            &$shortClassLikeNames
        ) {
            // ...
            if (! $node instanceof ClassLike) {
                return null;
            }

            if ($node->name === null) {
                return null;
            }

            /** @var string $classLikeName */
            $classLikeName = $this->nameResolver->getName($node->name);
            $shortClassLikeNames[] = $this->classNaming->getShortName($classLikeName);
        });

        return array_unique($shortClassLikeNames);
    }

    /**
     * @return string[]
     */
    private function resolveForNamespace(Namespace_ $node): array
    {
        $shortNames = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node->stmts, function (Node $node) use (
            &$shortNames
        ): void {
            // class name is used!
            if ($node instanceof ClassLike && $node->name instanceof Identifier) {
                $shortNames[$node->name->toString()] = $node->name->toString();
                return;
            }

            if (! $node instanceof Name) {
                return;
            }

            $originalName = $node->getAttribute('originalName');
            if (! $originalName instanceof Name) {
                return;
            }

            // already short
            if (Strings::contains($originalName->toString(), '\\')) {
                return;
            }

            $shortNames[$originalName->toString()] = $node->toString();
        });

        return $shortNames;
    }
}
