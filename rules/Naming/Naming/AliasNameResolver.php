<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
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
     * @param array<Use_|GroupUse> $uses
     */
    public function resolveByName(FullyQualified $fullyQualified, array $uses) : ?string
    {
        $nameString = $fullyQualified->toString();
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
                    continue;
                }
                $fullyQualified = $prefix . $useUse->name->toString();
                if ($fullyQualified !== $nameString) {
                    continue;
                }
                return (string) $useUse->getAlias();
            }
        }
        return null;
    }
}
