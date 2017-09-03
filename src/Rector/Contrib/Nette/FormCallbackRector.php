<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Deprecation\DeprecationInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Deprecation\SetNames;
use Rector\NodeFactory\NodeFactory;

/**
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject.
 */
final class FormCallbackRector extends NodeVisitorAbstract implements DeprecationInterface, RectorInterface
{
    /**
     * @var string
     */
    public const FORM_CLASS = 'Nette\Application\UI\Form';

    /**
     * @var Node
     */
    private $previousNode;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

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

            return $this->nodeFactory->createArray($node->var, $node->name);
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

        if ($node->var->getAttribute('type') !== self::FORM_CLASS) {
            return false;
        }

        $propertyName = (string) $node->name;
        if (! in_array($propertyName, ['onSuccess', 'onSubmit'], true)) {
            return false;
        }

        return true;
    }
}
