<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject.
 */
final class FormCallbackRector extends AbstractRector
{
    /**
     * @var string
     */
    public const FORM_CLASS = 'Nette\Application\UI\Form';

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * Detects "$form->onSuccess[] = $this->someAction;"
     */
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $this->isFormEvent($node)) {
            return false;
        }

        if (! $this->isFormEventHandler($node->var->var)) {
            return false;
        }

        if (! $node->expr instanceof PropertyFetch) {
            return false;
        }

        return true;
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->expr = $this->nodeFactory->createArray($node->expr->var, $node->expr->name);

        return $node;
    }

    private function isFormEventHandler(PropertyFetch $propertyFetchNode): bool
    {
        if ($propertyFetchNode->var->getAttribute(Attribute::TYPE) !== self::FORM_CLASS) {
            return false;
        }

        $propertyName = (string) $propertyFetchNode->name;

        return in_array($propertyName, ['onSuccess', 'onSubmit', 'onError', 'onRender'], true);
    }

    private function isFormEvent(Assign $assignNode): bool
    {
        if (! $assignNode->var instanceof ArrayDimFetch) {
            return false;
        }

        if (! $assignNode->var->var instanceof PropertyFetch) {
            return false;
        }

        return true;
    }
}
