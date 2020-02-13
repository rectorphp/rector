<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Covers https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 * @see \Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\StringFormTypeToClassRectorTest
 */
final class StringFormTypeToClassRector extends AbstractRector
{
    /**
     * @var FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;

    public function __construct(FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }

    /**
     * @todo add custom form types
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony',
            [
                new CodeSample(
<<<'PHP'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', 'form.type.text');
PHP
                    ,
                    <<<'PHP'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
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
        if (! $this->isObjectType($node->var, FormBuilderInterface::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'add')) {
            return null;
        }

        // just one argument
        if (! isset($node->args[1])) {
            return null;
        }

        // not a string
        if (! $node->args[1]->value instanceof String_) {
            return null;
        }

        /** @var String_ $stringNode */
        $stringNode = $node->args[1]->value;

        // not a form type string
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringNode->value);
        if ($formClass === null) {
            return null;
        }

        $node->args[1]->value = $this->createClassConstantReference($formClass);

        return $node;
    }
}
