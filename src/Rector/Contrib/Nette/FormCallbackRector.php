<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Deprecation\DeprecationInterface;
use Rector\Deprecation\SetNames;

/**
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 */
final class FormCallbackRector extends NodeVisitorAbstract implements DeprecationInterface
{
    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    public function enterNode(Node $node): ?int
    {
        if ($this->isCandidate($node)) {
            return false;
            dump($node); // get next node!
            die;

            $this->refactor($node);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    private function isCandidate(Node $node): bool
    {
        return $node instanceof Node\Expr\PropertyFetch;
    }
}
