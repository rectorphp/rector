<?php declare(strict_types=1);

namespace Rector\NodeVisitor\UpgradeDeprecation;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\StatementGlue;

/**
 * Reflects @link https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 */
final class DeprecatedParentClassToTraitNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var StatementGlue
     */
    private $statementGlue;

    public function __construct(StatementGlue $statementGlue)
    {
        $this->statementGlue = $statementGlue;
    }

    public function getParentClassName(): string
    {
        return 'Nette\Object';
    }

    public function getTraitName(): string
    {
        return 'Nette\SmartObject';
    }

    public function enterNode(Node $node): ?int
    {
        if ($this->isCandidate($node)) {
            $this->refactor($node);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    private function isCandidate(Node $node): bool
    {
        if ($node instanceof Class_) {
            if (! $node->extends) {
                return false;
            }

            $parentClassName = (string) $node->extends;
            if ($parentClassName !== $this->getParentClassName()) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function refactor(Class_ $classNode): void
    {
        // remove parent class
        $classNode->extends = null;

        $traitUseNode = $this->createTraitUse($this->getTraitName());
        $this->statementGlue->addAsFirstTrait($classNode, $traitUseNode);
    }

    private function createTraitUse(string $traitName): TraitUse
    {
        $nameParts = explode('\\', $traitName);

        return new TraitUse([
            new FullyQualified($nameParts)
        ]);
    }
}
