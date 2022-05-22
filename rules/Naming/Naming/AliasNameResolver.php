<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
final class AliasNameResolver
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(\Rector\Naming\Naming\UseImportsResolver $useImportsResolver)
    {
        $this->useImportsResolver = $useImportsResolver;
    }
    public function resolveByName(\PhpParser\Node\Name $name) : ?string
    {
        $uses = $this->useImportsResolver->resolveForNode($name);
        $nameString = $name->toString();
        foreach ($uses as $use) {
            $prefix = $use instanceof \PhpParser\Node\Stmt\GroupUse ? $use->prefix . '\\' : '';
            $useUses = $use->uses;
            foreach ($useUses as $useUse) {
                if (!$useUse->alias instanceof \PhpParser\Node\Identifier) {
                    continue;
                }
                $name = $prefix . $useUse->name->toString();
                if ($name !== $nameString) {
                    continue;
                }
                return (string) $useUse->getAlias();
            }
        }
        return null;
    }
}
