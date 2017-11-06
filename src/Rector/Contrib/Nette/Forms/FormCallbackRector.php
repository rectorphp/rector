<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
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

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    public function __construct(NodeFactory $nodeFactory, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeFactory = $nodeFactory;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
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

        /** @var ArrayDimFetch $arrayDimFetchNode */
        $arrayDimFetchNode = $node->var;

        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $arrayDimFetchNode->var;
        if (! $this->isFormEventHandler($propertyFetchNode)) {
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
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $node->expr;

        $node->expr = $this->nodeFactory->createArray($propertyFetchNode->var, $propertyFetchNode->name);

        return $node;
    }

    private function isFormEventHandler(PropertyFetch $propertyFetchNode): bool
    {
        return $this->propertyFetchAnalyzer->isTypeAndProperties(
            $propertyFetchNode,
            self::FORM_CLASS,
            ['onSuccess', 'onSubmit', 'onError', 'onRender']
        );
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
