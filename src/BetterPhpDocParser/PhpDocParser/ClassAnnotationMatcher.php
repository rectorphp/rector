<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * Matches "@ORM\Entity" to FQN names based on use imports in the file
 */
final class ClassAnnotationMatcher
{
    /**
     * @readonly
     */
    private UseImportNameMatcher $useImportNameMatcher;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var array<non-empty-string, string>
     */
    private array $fullyQualifiedNameByHash = [];
    public function __construct(UseImportNameMatcher $useImportNameMatcher, UseImportsResolver $useImportsResolver, ReflectionProvider $reflectionProvider)
    {
        $this->useImportNameMatcher = $useImportNameMatcher;
        $this->useImportsResolver = $useImportsResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return non-empty-string
     */
    public function resolveTagFullyQualifiedName(string $tag, Node $node) : string
    {
        $uniqueId = $tag . \spl_object_id($node);
        if (isset($this->fullyQualifiedNameByHash[$uniqueId])) {
            return $this->fullyQualifiedNameByHash[$uniqueId];
        }
        $tag = \ltrim($tag, '@');
        $uses = $this->useImportsResolver->resolve();
        $fullyQualifiedClass = $this->resolveFullyQualifiedClass($uses, $node, $tag);
        if ($fullyQualifiedClass === null) {
            $fullyQualifiedClass = $tag;
        }
        $this->fullyQualifiedNameByHash[$uniqueId] = $fullyQualifiedClass;
        return $fullyQualifiedClass;
    }
    /**
     * @param array<Use_|GroupUse> $uses
     * @return non-empty-string|null
     */
    private function resolveFullyQualifiedClass(array $uses, Node $node, string $tag) : ?string
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope) {
            $namespace = $scope->getNamespace();
            if ($namespace !== null) {
                $namespacedTag = $namespace . '\\' . $tag;
                if ($this->reflectionProvider->hasClass($namespacedTag)) {
                    return $namespacedTag;
                }
                if (\strpos($tag, '\\') === \false) {
                    return $this->resolveAsAliased($uses, $tag);
                }
                if ($this->isPreslashedExistingClass($tag)) {
                    // Global or absolute Class
                    return $tag;
                }
            }
        }
        return $this->useImportNameMatcher->matchNameWithUses($tag, $uses);
    }
    /**
     * @param array<Use_|GroupUse> $uses
     * @return non-empty-string|null
     */
    private function resolveAsAliased(array $uses, string $tag) : ?string
    {
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
                    continue;
                }
                if ($useUse->alias->toString() === $tag) {
                    return $prefix . $useUse->name->toString();
                }
            }
        }
        return $this->useImportNameMatcher->matchNameWithUses($tag, $uses);
    }
    private function isPreslashedExistingClass(string $tag) : bool
    {
        if (\strncmp($tag, '\\', \strlen('\\')) !== 0) {
            return \false;
        }
        return $this->reflectionProvider->hasClass($tag);
    }
}
