<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;

/**
 * @see https://symfony.com/doc/2.8/form/form_collections.html
 * @see https://symfony.com/doc/3.0/form/form_collections.html
 * @see https://symfony2-document.readthedocs.io/en/latest/reference/forms/types/collection.html#type
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRectorTest
 */
final class ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector extends AbstractRector
{
    /**
     * @var FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;

    public function __construct(FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change type in CollectionType from alias string to class reference', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP

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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $options = $node->args[2]->value;
        if (! $options instanceof Array_) {
            return null;
        }

        foreach ($options->items as $optionsArrayItem) {
            if ($optionsArrayItem->key === null) {
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

    private function isCollectionType(MethodCall $methodCall): bool
    {
        $typeValue = $methodCall->args[1]->value;

        if ($typeValue instanceof ClassConstFetch && $this->isName(
            $typeValue->class,
            'Symfony\Component\Form\Extension\Core\Type\CollectionType'
        )) {
            return true;
        }

        return $typeValue instanceof String_ && $this->isValue($typeValue, 'collection');
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->isObjectType($methodCall->var, 'Symfony\Component\Form\FormBuilderInterface')) {
            return true;
        }

        if (! $this->isName($methodCall->name, 'add')) {
            return true;
        }

        // just one argument
        if (! isset($methodCall->args[1])) {
            return true;
        }

        if (! $this->isCollectionType($methodCall)) {
            return true;
        }

        // no options
        return ! isset($methodCall->args[2]);
    }
}
