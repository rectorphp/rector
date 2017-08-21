<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Deprecation\DeprecationInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Deprecation\SetNames;

/**
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject.
 */
final class FormCallbackRector extends NodeVisitorAbstract implements DeprecationInterface, RectorInterface
{
    /**
     * @var Node
     */
    private $previousNode;

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    /**
     * @return null|int|Node
     */
    public function enterNode(Node $node)
    {
        if ($this->previousNode && $this->isFormEventAssign($this->previousNode)) {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            return $this->createShortArray($node);
        }

        $this->previousNode = $node;
        if ($this->isFormEventAssign($node)) {
            // do not check children, just go to next token
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    public function isCandidate(Node $node): bool
    {
        return true;
    }

    public function refactor(Node $node): ?Node
    {
        return $node;
    }

    private function isFormEventAssign(Node $node): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        if ($node->var->getAttribute('type') !== $this->getDesiredClass()) {
            return false;
        }

        $propertyName = (string) $node->name;
        if (! in_array($propertyName, ['onSuccess', 'onSubmit'], true)) {
            return false;
        }

        return true;
    }

    /**
     * [$this, 'something']
     */
    private function createShortArray(Node $node): Array_
    {
        return new Array_([
            new ArrayItem($node->var),
            new ArrayItem(
                new String_(
                    (string) $node->name
                )
            ),
        ], [
            'kind' => Array_::KIND_SHORT,
        ]);
    }

    private function getDesiredClass(): string
    {
        return 'Nette\Application\UI\Form';
    }
}
