<?php declare(strict_types=1);

namespace Rector\NodeVisitor\UpgradeDeprecation;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Reflects @link https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 */
final class DeprecatedParentClassToTraitNodeVisitor extends NodeVisitorAbstract
{
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
        if ($node instanceof Node\Stmt\Class_) {
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

    private function refactor(Node\Stmt\Class_ $classNode): void
    {
        // remove parent class
        $classNode->extends = null;

        // add new trait
        $nameParts = explode('\\', $this->getTraitName());
        $classNode->stmts[] = new TraitUse([
            new FullyQualified($nameParts)
        ]);
    }
}
