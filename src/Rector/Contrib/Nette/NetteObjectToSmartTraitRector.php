<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Builder\StatementGlue;
use Rector\Deprecation\SetNames;
use Rector\NodeTraverser\TokenSwitcher;
use Rector\Rector\AbstractRector;

/**
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject.
 */
final class NetteObjectToSmartTraitRector extends AbstractRector
{
    /**
     * @var StatementGlue
     */
    private $statementGlue;

    /**
     * @var TokenSwitcher
     */
    private $tokenSwitcher;

    public function __construct(StatementGlue $statementGlue, TokenSwitcher $tokenSwitcher)
    {
        $this->statementGlue = $statementGlue;
        $this->tokenSwitcher = $tokenSwitcher;
    }

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.2;
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof Class_) {
            if (! $node->extends) {
                return false;
            }

            $parentClassName = (string) $node->extends;
            if ($parentClassName !== 'Nette\Object') {
                return false;
            }

            $this->tokenSwitcher->enable();

            return true;
        }

        return false;
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        $traitUseNode = $this->createTraitUse('Nette\SmartObject');
        $this->statementGlue->addAsFirstTrait($classNode, $traitUseNode);

        // remove parent class
        $classNode->extends = null;

        return $classNode;
    }

    private function createTraitUse(string $traitName): TraitUse
    {
        return new TraitUse([
            new FullyQualified($traitName),
        ]);
    }
}
