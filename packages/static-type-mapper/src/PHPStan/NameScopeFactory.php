<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\NameScope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
 */
final class NameScopeFactory
{
    public function createNameScopeFromNode(Node $node): NameScope
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NAME);

        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(AttributeKey::USE_NODES);

        $uses = $this->resolveUseNamesByAlias($useNodes);
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        return new NameScope($namespace, $uses, $className);
    }

    /**
     * @param Use_[] $useNodes
     * @return array<string, string>
     */
    private function resolveUseNamesByAlias(array $useNodes): array
    {
        $useNamesByAlias = [];

        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                /** @var UseUse $useUse */
                $aliasName = $useUse->getAlias()
                    ->name;

                $useName = $useUse->name->toString();
                if (! is_string($useName)) {
                    throw new ShouldNotHappenException();
                }

                // uses must be lowercase, as PHPStan lowercases it
                $lowercasedAliasName = strtolower($aliasName);

                $useNamesByAlias[$lowercasedAliasName] = $useName;
            }
        }

        return $useNamesByAlias;
    }
}
