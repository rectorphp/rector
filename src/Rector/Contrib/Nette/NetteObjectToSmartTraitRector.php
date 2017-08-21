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
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject.
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
        if (! $node instanceof Class_) {
            return false;
        }

        if (! $node->extends) {
            return false;
        }

        $parentClassName = $this->getParentClassName($node);

        return $parentClassName === $this->getParentClass();
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        $traitUseNode = $this->createTraitUse($this->getTraitName());
        $this->statementGlue->addAsFirstTrait($classNode, $traitUseNode);

        $this->removeParentClass($classNode);

        return $classNode;
    }

    private function createTraitUse(string $traitName): TraitUse
    {
        return new TraitUse([
            new FullyQualified($traitName),
        ]);
    }

    private function getParentClass(): string
    {
        return 'Nette\Object';
    }

    private function getTraitName(): string
    {
        return 'Nette\SmartObject';
    }

    private function getParentClassName(Class_ $classNode): string
    {
        $parentClass = $classNode->extends;

        /** @var FullyQualified $fqnParentClassName */
        $fqnParentClassName = $parentClass->getAttribute('resolvedName');

        return $fqnParentClassName->toString();
    }

    private function removeParentClass(Class_ $classNode): void
    {
        $classNode->extends = null;
    }
}
