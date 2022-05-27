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
     * @var array<string, string>
     */
    private $fullyQualifiedNameByHash = [];
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher
     */
    private $useImportNameMatcher;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(UseImportNameMatcher $useImportNameMatcher, UseImportsResolver $useImportsResolver, ReflectionProvider $reflectionProvider)
    {
        $this->useImportNameMatcher = $useImportNameMatcher;
        $this->useImportsResolver = $useImportsResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveTagFullyQualifiedName(string $tag, Node $node) : string
    {
        $uniqueHash = $tag . \spl_object_hash($node);
        if (isset($this->fullyQualifiedNameByHash[$uniqueHash])) {
            return $this->fullyQualifiedNameByHash[$uniqueHash];
        }
        $tag = \ltrim($tag, '@');
        $uses = $this->useImportsResolver->resolveForNode($node);
        $fullyQualifiedClass = $this->resolveFullyQualifiedClass($uses, $node, $tag);
        $this->fullyQualifiedNameByHash[$uniqueHash] = $fullyQualifiedClass;
        return $fullyQualifiedClass;
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    private function resolveFullyQualifiedClass(array $uses, Node $node, string $tag) : string
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
            }
        }
        return $this->useImportNameMatcher->matchNameWithUses($tag, $uses) ?? $tag;
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    private function resolveAsAliased(array $uses, string $tag) : string
    {
        foreach ($uses as $use) {
            $prefix = $use instanceof GroupUse ? $use->prefix . '\\' : '';
            $useUses = $use->uses;
            foreach ($useUses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
                    continue;
                }
                $alias = $useUse->alias;
                if ($alias->toString() === $tag) {
                    return $prefix . $useUse->name->toString();
                }
            }
        }
        return $this->useImportNameMatcher->matchNameWithUses($tag, $uses) ?? $tag;
    }
}
