<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony27\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Symfony27\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRectorTest
 */
final class ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer;
    /**
     * @readonly
     */
    private FormOptionsArrayMatcher $formOptionsArrayMatcher;
    /**
     * @readonly
     */
    private FormCollectionAnalyzer $formCollectionAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_OPTION_NAME = ['type' => 'entry_type', 'options' => 'entry_options'];
    public function __construct(FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, FormOptionsArrayMatcher $formOptionsArrayMatcher, FormCollectionAnalyzer $formCollectionAnalyzer, ValueResolver $valueResolver)
    {
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
        $this->formCollectionAnalyzer = $formCollectionAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename `type` option to `entry_type` in CollectionType', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        if (!$this->formCollectionAnalyzer->isCollectionType($node)) {
            return null;
        }
        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (!$optionsArray instanceof Array_) {
            return null;
        }
        $hasChanged = $this->refactorOptionsArray($optionsArray);
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function refactorOptionsArray(Array_ $optionsArray) : bool
    {
        $hasChanged = \false;
        foreach ($optionsArray->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof Expr) {
                continue;
            }
            foreach (self::OLD_TO_NEW_OPTION_NAME as $oldName => $newName) {
                if (!$this->valueResolver->isValue($arrayItem->key, $oldName)) {
                    continue;
                }
                $arrayItem->key = new String_($newName);
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
}
