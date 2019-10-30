<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\Form\Form;

/**
 * @see \Rector\Symfony\Tests\Rector\Form\FormIsValidRector\FormIsValidRectorTest
 */
final class FormIsValidRector extends AbstractRector
{
    /**
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    public function __construct(MethodCallManipulator $methodCallManipulator)
    {
        $this->methodCallManipulator = $methodCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony',
            [
                new CodeSample(
                    <<<'PHP'
if ($form->isValid()) {
}
PHP
                    ,
<<<'PHP'
if ($form->isSubmitted() && $form->isValid()) {
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipMethodCall($node)) {
            return null;
        }

        /** @var Variable $variable */
        $variable = $node->var;
        if ($this->isIsSubmittedByAlreadyCalledOnVariable($variable)) {
            return null;
        }

        /** @var string $variableName */
        $variableName = $this->getName($node->var);

        return new BooleanAnd(
            $this->createMethodCall($variableName, 'isSubmitted'),
            $this->createMethodCall($variableName, 'isValid')
        );
    }

    private function shouldSkipMethodCall(MethodCall $methodCall): bool
    {
        // skip just added calls
        if ($methodCall->getAttribute(AttributeKey::ORIGINAL_NODE) === null) {
            return true;
        }

        if (! $this->isObjectType($methodCall->var, Form::class)) {
            return true;
        }

        if (! $this->isName($methodCall->name, 'isValid')) {
            return true;
        }

        if ($methodCall->getAttribute(AttributeKey::PREVIOUS_NODE) !== null) {
            return true;
        }

        $variableName = $this->getName($methodCall->var);
        return $variableName === null;
    }

    private function isIsSubmittedByAlreadyCalledOnVariable(Variable $variable): bool
    {
        $previousMethodCallNamesOnVariable = $this->methodCallManipulator->findMethodCallNamesOnVariable($variable);

        // already checked by isSubmitted()
        return in_array('isSubmitted', $previousMethodCallNamesOnVariable, true);
    }
}
