<?php

declare (strict_types=1);
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
    private $fullyQualifiedNameByHash = [];
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher
     */
    private $useImportNameMatcher;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher $useImportNameMatcher, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->useImportNameMatcher = $useImportNameMatcher;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveTagFullyQualifiedName(string $tag, \PhpParser\Node $node) : string
    {
        $uniqueHash = $tag . \spl_object_hash($node);
        if (isset($this->fullyQualifiedNameByHash[$uniqueHash])) {
            return $this->fullyQualifiedNameByHash[$uniqueHash];
        }
        $tag = \ltrim($tag, '@');
        /** @var Use_[] $uses */
        $uses = (array) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES);
        $fullyQualifiedClass = $this->resolveFullyQualifiedClass($uses, $node, $tag);
        $this->fullyQualifiedNameByHash[$uniqueHash] = $fullyQualifiedClass;
        return $fullyQualifiedClass;
    }
    /**
     * @param Use_[] $uses
     */
    private function resolveFullyQualifiedClass(array $uses, \PhpParser\Node $node, string $tag) : string
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($scope instanceof \PHPStan\Analyser\Scope) {
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
     * @param Use_[] $uses
     */
    private function resolveAsAliased(array $uses, string $tag) : string
    {
        foreach ($uses as $use) {
            $useUses = $use->uses;
            // skip group uses or empty
            if (\count($useUses) !== 1) {
                continue;
            }
            // uses already removed
            if (!isset($useUses[0])) {
                continue;
            }
            if (!$useUses[0]->alias instanceof \PhpParser\Node\Identifier) {
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
