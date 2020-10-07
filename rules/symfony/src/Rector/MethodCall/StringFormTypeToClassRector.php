<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * Covers https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\StringFormTypeToClassRector\StringFormTypeToClassRectorTest
 */
final class StringFormTypeToClassRector extends AbstractFormAddRector
{
    /**
     * @var string
     */
    private const DESCRIPTION = 'Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "$parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, ...);"';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(self::DESCRIPTION, [
            new CodeSample(
<<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', 'form.type.text');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
CODE_SAMPLE
            ),
        ]);
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
        if (! $this->isFormAddMethodCall($node)) {
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
