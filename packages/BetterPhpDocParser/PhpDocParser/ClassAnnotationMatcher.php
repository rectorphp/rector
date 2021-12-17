<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Matches "@ORM\Entity" to FQN names based on use imports in the file
 */
final class ClassAnnotationMatcher
{
    /**
     * @var array<string, string>
     */
    private array $fullyQualifiedNameByHash = [];

    public function __construct(
        private readonly UseImportNameMatcher $useImportNameMatcher,
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function resolveTagFullyQualifiedName(string $tag, Node $node): string
    {
        $uniqueHash = $tag . spl_object_hash($node);
        if (isset($this->fullyQualifiedNameByHash[$uniqueHash])) {
            return $this->fullyQualifiedNameByHash[$uniqueHash];
        }

        $tag = ltrim($tag, '@');

        /** @var Use_[] $uses */
        $uses = (array) $node->getAttribute(AttributeKey::USE_NODES);
        $fullyQualifiedClass = $this->resolveFullyQualifiedClass($uses, $node, $tag);

        $this->fullyQualifiedNameByHash[$uniqueHash] = $fullyQualifiedClass;

        return $fullyQualifiedClass;
    }

    /**
     * @param Use_[] $uses
     */
    private function resolveFullyQualifiedClass(array $uses, Node $node, string $tag): string
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if ($scope instanceof Scope) {
            $namespace = $scope->getNamespace();
            if ($namespace !== null) {
                $namespacedTag = $namespace . '\\' . $tag;
                if ($this->reflectionProvider->hasClass($namespacedTag)) {
                    return $namespacedTag;
                }

                if (! str_contains($tag, '\\')) {
                    return $this->resolveAsAliased($uses, $tag);
                }
            }
        }

        return $this->useImportNameMatcher->matchNameWithUses($tag, $uses) ?? $tag;
    }

    /**
     * @param Use_[] $uses
     */
    private function resolveAsAliased(array $uses, string $tag): string
    {
        foreach ($uses as $use) {
            $useUses = $use->uses;
            // skip group uses or empty
            if (count($useUses) !== 1) {
                continue;
            }

            // uses already removed
            if (! isset($useUses[0])) {
                continue;
            }

            if (! $useUses[0]->alias instanceof Identifier) {
                continue;
            }

            $alias = $useUses[0]->alias;
            if ($alias->toString() === $tag) {
                return $useUses[0]->name->toString();
            }
        }

        return $this->useImportNameMatcher->matchNameWithUses($tag, $uses) ?? $tag;
    }
}
