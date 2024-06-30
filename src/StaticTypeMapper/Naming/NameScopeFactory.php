<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
 */
final class NameScopeFactory
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(UseImportsResolver $useImportsResolver)
    {
        $this->useImportsResolver = $useImportsResolver;
    }
    public function createNameScopeFromNodeWithoutTemplateTypes(Node $node) : NameScope
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $namespace = $scope instanceof Scope ? $scope->getNamespace() : null;
        $uses = $this->useImportsResolver->resolve();
        $usesAliasesToNames = $this->resolveUseNamesByAlias($uses);
        if ($scope instanceof Scope && $scope->getClassReflection() instanceof ClassReflection) {
            $classReflection = $scope->getClassReflection();
            $className = $classReflection->getName();
        } else {
            $className = null;
        }
        return new NameScope($namespace, $usesAliasesToNames, $className);
    }
    public function createNameScopeFromNode(Node $node) : NameScope
    {
        $nameScope = $this->createNameScopeFromNodeWithoutTemplateTypes($node);
        /** @var non-empty-string|null $namespace */
        $namespace = $nameScope->getNamespace();
        return new NameScope($namespace, $nameScope->getUses(), $nameScope->getClassName(), null, null);
    }
    /**
     * @param array<Use_|GroupUse> $useNodes
     * @return array<string, string>
     */
    private function resolveUseNamesByAlias(array $useNodes) : array
    {
        $useNamesByAlias = [];
        foreach ($useNodes as $useNode) {
            $prefix = $this->useImportsResolver->resolvePrefix($useNode);
            foreach ($useNode->uses as $useUse) {
                /** @var UseUse $useUse */
                $aliasName = $useUse->getAlias()->name;
                // uses must be lowercase, as PHPStan lowercases it
                $lowercasedAliasName = \strtolower($aliasName);
                $useNamesByAlias[$lowercasedAliasName] = $prefix . $useUse->name->toString();
            }
        }
        return $useNamesByAlias;
    }
}
