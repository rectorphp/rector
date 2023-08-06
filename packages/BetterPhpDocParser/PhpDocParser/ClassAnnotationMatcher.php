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
    /**
     * @var array<string, string>
     */
    private $fullyQualifiedNameByHash = [];
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
        $uses = $this->useImportsResolver->resolve();
        $fullyQualifiedClass = $this->resolveFullyQualifiedClass($uses, $node, $tag, \false);
        if ($fullyQualifiedClass === null) {
            $fullyQualifiedClass = $tag;
        }
        $this->fullyQualifiedNameByHash[$uniqueHash] = $fullyQualifiedClass;
        return $fullyQualifiedClass;
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    private function resolveFullyQualifiedClass(array $uses, Node $node, string $tag, bool $returnNullOnUnknownClass) : ?string
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
                    return $this->resolveAsAliased($uses, $tag, $returnNullOnUnknownClass);
                }
                if ($this->isPreslashedExistingClass($tag)) {
                    // Global or absolute Class
                    return $tag;
                }
            }
        }
        $class = $this->useImportNameMatcher->matchNameWithUses($tag, $uses);
        return $this->resolveClass($class, $returnNullOnUnknownClass);
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    private function resolveAsAliased(array $uses, string $tag, bool $returnNullOnUnknownClass) : ?string
    {
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
                    continue;
                }
                if ($useUse->alias->toString() === $tag) {
                    $class = $prefix . $useUse->name->toString();
                    return $this->resolveClass($class, $returnNullOnUnknownClass);
                }
            }
        }
        $class = $this->useImportNameMatcher->matchNameWithUses($tag, $uses);
        return $this->resolveClass($class, $returnNullOnUnknownClass);
    }
    private function resolveClass(?string $class, bool $returnNullOnUnknownClass) : ?string
    {
        if ($class === null) {
            return null;
        }
        $resolvedClass = $this->reflectionProvider->hasClass($class) ? $class : null;
        return $returnNullOnUnknownClass ? $resolvedClass : $class;
    }
    private function isPreslashedExistingClass(string $tag) : bool
    {
        if (\strncmp($tag, '\\', \strlen('\\')) !== 0) {
            return \false;
        }
        return $this->reflectionProvider->hasClass($tag);
    }
}
