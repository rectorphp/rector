<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Collector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\VirtualNode;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;

final class TraitNodeScopeCollector
{
    /**
     * @var array<string, Scope>
     */
    private array $scopeByTraitNodeHash = [];

    public function __construct(
        private BetterStandardPrinter $betterStandardPrinter
    ) {
    }

    public function addForTraitAndNode(string $traitName, Node $node, Scope $scope): void
    {
        if ($node instanceof VirtualNode) {
            return;
        }

        $traitNodeHash = $this->createHash($traitName, $node);

        // probably set from another class
        if (isset($this->scopeByTraitNodeHash[$traitNodeHash])) {
            return;
        }

        $this->scopeByTraitNodeHash[$traitNodeHash] = $scope;
    }

    public function getScopeForTraitAndNode(string $traitName, Node $node): ?Scope
    {
        $traitNodeHash = $this->createHash($traitName, $node);

        return $this->scopeByTraitNodeHash[$traitNodeHash] ?? null;
    }

    private function createHash(string $traitName, Node $node): string
    {
        $printedNode = $this->betterStandardPrinter->print($node);

        return sha1($traitName . $printedNode);
    }
}
