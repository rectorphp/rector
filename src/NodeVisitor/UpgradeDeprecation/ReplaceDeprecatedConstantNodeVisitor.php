<?php declare(strict_types=1);

namespace Rector\NodeVisitor\UpgradeDeprecation;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class ReplaceDeprecatedConstantNodeVisitor extends NodeVisitorAbstract
{
    // this will be in specific node visitor, now hardcoded

    public function getClassName(): string
    {
        return'ClassWithConstants';
    }

    public function getOldConstantName(): string
    {
        return 'OLD_CONSTANT';
    }

    public function getNewConstantName(): string
    {
        return 'NEW_CONSTANT';
    }

    /**
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::DONT_TRAVERSE_CHILDREN
     *        => Children of $node are not traversed. $node stays as-is
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * otherwise
     *        => $node is set to the return value.
     *
     * @return null|int|Node
     */
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
