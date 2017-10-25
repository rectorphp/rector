<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * $form->isValid()
 *
 * into:
 * $form->isSubmitted() && $form->isValid()
 */
final class FormIsValidRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if ($node->var->getAttribute(Attribute::TYPES) !== 'Symfony\Component\Form\Form') {
            return false;
        }

        if ((string) $node->name !== 'isValid') {
            return false;
        }

        if ($node->getAttribute(Attribute::PREVIOUS_NODE) !== null) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $variableName = $node->var->name;

        return new BooleanAnd(
            $this->nodeFactory->createMethodCall($variableName, 'isSubmitted'),
            $this->nodeFactory->createMethodCall($variableName, 'isValid')
        );
    }
}
