<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AliasNameResolver
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function resolveByName(Name $name): ?string
    {
        /** @var Use_[] $useNodes */
        $useNodes = $name->getAttribute(AttributeKey::USE_NODES);
        $nameString = $name->toString();

        foreach ($useNodes as $useNode) {
            $useUses = $useNode->uses;
            if (count($useUses) > 1) {
                continue;
            }

            if (! isset($useUses[0])) {
                continue;
            }

            $useUse = $useUses[0];
            if (! $useUse->alias instanceof Identifier) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($useUse->name, $nameString)) {
                continue;
            }

            return (string) $useUse->getAlias();
        }

        return null;
    }
}
