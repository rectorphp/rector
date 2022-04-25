<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\NodeNameResolver\NodeNameResolver;
final class AliasNameResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\Naming\UseImportsResolver $useImportsResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function resolveByName(\PhpParser\Node\Name $name) : ?string
    {
        $uses = $this->useImportsResolver->resolveForNode($name);
        $nameString = $name->toString();
        foreach ($uses as $use) {
            $useUses = $use->uses;
            if (\count($useUses) > 1) {
                continue;
            }
            if (!isset($useUses[0])) {
                continue;
            }
            $useUse = $useUses[0];
            if (!$useUse->alias instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($useUse->name, $nameString)) {
                continue;
            }
            return (string) $useUse->getAlias();
        }
        return null;
    }
}
