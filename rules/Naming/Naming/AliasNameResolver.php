<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\NodeNameResolver\NodeNameResolver;

final class AliasNameResolver
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly UseImportsResolver $useImportsResolver,
    ) {
    }

    public function resolveByName(Name $name): ?string
    {
        $uses = $this->useImportsResolver->resolveForNode($name);
        $nameString = $name->toString();

        foreach ($uses as $use) {
            $useUses = $use->uses;
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
