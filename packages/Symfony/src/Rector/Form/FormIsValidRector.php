<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\Form\Form;

/**
 * @see \Rector\Symfony\Tests\Rector\Form\FormIsValidRector\FormIsValidRectorTest
 */
final class FormIsValidRector extends AbstractRector
{
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
        // skip just added calls
        if ($node->getAttribute(AttributeKey::ORIGINAL_NODE) === null) {
            return null;
        }

        if (! $this->isObjectType($node, Form::class)) {
            return null;
        }

        if (! $this->isName($node, 'isValid')) {
            return null;
        }

        if ($node->getAttribute(AttributeKey::PREVIOUS_NODE) !== null) {
            return null;
        }

        $variableName = $this->getName($node->var);
        if ($variableName === null) {
            return null;
        }

        return new BooleanAnd(
            $this->createMethodCall($variableName, 'isSubmitted'),
            $this->createMethodCall($variableName, 'isValid')
        );
    }
}
