<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Configuration\Option;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Symfony\Rector\MethodCall\AbstractFormAddRector;

/**
 * Covers https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\StringFormTypeToClassRectorTest
 */
final class StringFormTypeToClassRector extends AbstractFormAddRector
{
    public function getDefinition(): RectorDefinition
    {
        $description = sprintf(
            'Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "parameters > %s"',
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER
        );

        return new RectorDefinition(
            $description,
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
