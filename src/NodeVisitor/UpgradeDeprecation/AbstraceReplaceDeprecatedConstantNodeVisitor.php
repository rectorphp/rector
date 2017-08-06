<?php declare(strict_types=1);

namespace Rector\NodeVisitor\UpgradeDeprecation;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

abstract class AbstraceReplaceDeprecatedConstantNodeVisitor extends NodeVisitorAbstract
{
    abstract public function getClassName(): string;

    abstract public function getOldConstantName(): string;

    abstract public function getNewConstantName(): string;

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
        if ($node instanceof ClassConstFetch) {
            if ((string) $node->class !== $this->getClassName()) {
                return false;
            }

            if ((string) $node->name !== $this->getOldConstantName()) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function refactor(ClassConstFetch $classConstFetchNode): void
    {
        $classConstFetchNode->name->name = $this->getNewConstantName();
    }
}
