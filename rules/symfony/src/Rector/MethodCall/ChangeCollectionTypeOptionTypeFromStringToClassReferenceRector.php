<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://symfony.com/doc/2.8/form/form_collections.html
 * @see https://symfony.com/doc/3.0/form/form_collections.html
 * @see https://symfony2-document.readthedocs.io/en/latest/reference/forms/types/collection.html#type
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRectorTest
 */
final class ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector extends AbstractFormAddRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change type in CollectionType from alias string to class reference', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => 'choice',
        ]);

        $builder->add('tags', 'collection', [
            'type' => 'choice',
        ]);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);

        $builder->add('tags', 'collection', [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);
    }
}
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

        if (! $this->isCollectionType($node)) {
            return null;
        }

        $optionsArray = $this->matchOptionsArray($node);
        if ($optionsArray === null) {
            return null;
        }

        foreach ($optionsArray->items as $optionsArrayItem) {
            if ($optionsArrayItem === null || $optionsArrayItem->key === null) {
                continue;
            }

            if (! $this->isValues($optionsArrayItem->key, ['type', 'entry_type'])) {
                continue;
            }

            // already ::class reference
            if (! $optionsArrayItem->value instanceof String_) {
                return null;
            }

            $stringValue = $optionsArrayItem->value->value;
            $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringValue);
            if ($formClass === null) {
                return null;
            }

            $optionsArrayItem->value = $this->createClassConstantReference($formClass);
        }

        return $node;
    }
}
