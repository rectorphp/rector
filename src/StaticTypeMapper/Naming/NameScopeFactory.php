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
        if ($scope instanceof Scope) {
            $namespace = $scope->getNamespace();
            $classReflection = $scope->getClassReflection();
            $className = $classReflection instanceof ClassReflection ? $classReflection->getName() : null;
        } else {
            $namespace = null;
            $className = null;
        }
        $uses = $this->useImportsResolver->resolve();
        $usesAliasesToNames = $this->resolveUseNamesByAlias($uses);
        return new NameScope($namespace, $usesAliasesToNames, $className);
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
