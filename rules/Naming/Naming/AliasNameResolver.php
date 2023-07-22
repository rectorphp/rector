<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
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
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    public function resolveByName(Name $name, array $uses) : ?string
    {
        $nameString = $name->toString();
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
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
