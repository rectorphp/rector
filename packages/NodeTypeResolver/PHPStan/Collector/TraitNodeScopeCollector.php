<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Collector;

use PHPStan\Analyser\Scope;
final class TraitNodeScopeCollector
{
    /**
     * @var array<string, Scope>
     */
    private $scopeByTraitNodeHash = [];
    public function addForTrait(string $traitName, \PHPStan\Analyser\Scope $scope) : void
    {
        // probably set from another class
        if (isset($this->scopeByTraitNodeHash[$traitName])) {
            return;
        }
        $this->scopeByTraitNodeHash[$traitName] = $scope;
    }
    public function getScopeForTrait(string $traitName) : ?\PHPStan\Analyser\Scope
    {
        return $this->scopeByTraitNodeHash[$traitName] ?? null;
    }
}
