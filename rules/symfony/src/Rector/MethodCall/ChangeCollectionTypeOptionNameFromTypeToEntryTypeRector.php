<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRectorTest
 */
final class ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector extends AbstractFormAddRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_OPTION_NAME = [
        'type' => 'entry_type',
        'options' => 'entry_options',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename `type` option to `entry_type` in CollectionType', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => ChoiceType::class,
            'options' => [1, 2, 3],
        ]);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'entry_type' => ChoiceType::class,
            'entry_options' => [1, 2, 3],
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

        $this->refactorOptionsArray($optionsArray);

        return $node;
    }

    private function refactorOptionsArray(Array_ $optionsArray): void
    {
        foreach ($optionsArray->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }

            if ($arrayItem->key === null) {
                continue;
            }

            foreach (self::OLD_TO_NEW_OPTION_NAME as $oldName => $newName) {
                if (! $this->isValue($arrayItem->key, $oldName)) {
                    continue;
                }

                $arrayItem->key = new String_($newName);
            }
        }
    }
}
