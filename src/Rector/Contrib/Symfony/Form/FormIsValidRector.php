<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
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
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(MethodCallNodeFactory $methodCallNodeFactory, MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Component\Form\Form',
            'isValid'
        )) {
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
            $this->methodCallNodeFactory->createMethodCall($variableName, 'isSubmitted'),
            $this->methodCallNodeFactory->createMethodCall($variableName, 'isValid')
        );
    }
}
