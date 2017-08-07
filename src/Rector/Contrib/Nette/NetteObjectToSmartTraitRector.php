<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Builder\StatementGlue;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

/**
 * Reflects @link https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 */
final class NetteObjectToSmartTraitRector extends AbstractRector
{
    /**
     * @var StatementGlue
     */
    private $statementGlue;

    public function __construct(StatementGlue $statementGlue)
    {
        $this->statementGlue = $statementGlue;
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

            return true;
        }

        return false;
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor($classNode): void
    {
        // remove parent class
        $classNode->extends = null;

        $traitUseNode = $this->createTraitUse('Nette\SmartObject');
        $this->statementGlue->addAsFirstTrait($classNode, $traitUseNode);
    }

    private function createTraitUse(string $traitName): TraitUse
    {
        return new TraitUse([
            new FullyQualified($traitName)
        ]);
    }
}
