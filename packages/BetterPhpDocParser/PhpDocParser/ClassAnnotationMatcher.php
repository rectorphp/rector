<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
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
        private ReflectionProvider $reflectionProvider
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
            }
        }

        return $this->matchFullAnnotationClassWithUses($tag, $uses) ?? $tag;
    }

    /**
     * @param Use_[] $uses
     */
    private function matchFullAnnotationClassWithUses(string $tag, array $uses): ?string
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if (! $this->isUseMatchingName($tag, $useUse)) {
                    continue;
                }

                return $this->resolveName($tag, $useUse);
            }
        }

        return null;
    }

    private function isUseMatchingName(string $tag, UseUse $useUse): bool
    {
        $shortName = $useUse->alias !== null ? $useUse->alias->name : $useUse->name->getLast();
        $shortNamePattern = preg_quote($shortName, '#');

        return (bool) Strings::match($tag, '#' . $shortNamePattern . '(\\\\[\w]+)?$#i');
    }

    private function resolveName(string $tag, UseUse $useUse): string
    {
        if ($useUse->alias === null) {
            return $useUse->name->toString();
        }

        $unaliasedShortClass = Strings::substring($tag, Strings::length($useUse->alias->toString()));
        if (\str_starts_with($unaliasedShortClass, '\\')) {
            return $useUse->name . $unaliasedShortClass;
        }

        return $useUse->name . '\\' . $unaliasedShortClass;
    }
}
