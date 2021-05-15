<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/2.8/form/form_collections.html
 * @see https://symfony.com/doc/3.0/form/form_collections.html
 * @see https://symfony2-document.readthedocs.io/en/latest/reference/forms/types/collection.html#type
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector\ChangeStringCollectionOptionToConstantRectorTest
 */
final class ChangeStringCollectionOptionToConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;
    /**
     * @var \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer
     */
    private $formCollectionAnalyzer;
    public function __construct(\Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher $formOptionsArrayMatcher, \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider $formTypeStringToTypeProvider, \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer $formCollectionAnalyzer)
    {
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
        $this->formCollectionAnalyzer = $formCollectionAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change type in CollectionType from alias string to class reference', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        if (!$this->formCollectionAnalyzer->isCollectionType($node)) {
            return null;
        }
        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (!$optionsArray instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        return $this->processChangeToConstant($optionsArray, $node);
    }
    private function processChangeToConstant(\PhpParser\Node\Expr\Array_ $optionsArray, \PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        foreach ($optionsArray->items as $optionsArrayItem) {
            if ($optionsArrayItem === null) {
                continue;
            }
            if ($optionsArrayItem->key === null) {
                continue;
            }
            if (!$this->valueResolver->isValues($optionsArrayItem->key, ['type', 'entry_type'])) {
                continue;
            }
            // already ::class reference
            if (!$optionsArrayItem->value instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            $stringValue = $optionsArrayItem->value->value;
            $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringValue);
            if ($formClass === null) {
                return null;
            }
            $optionsArrayItem->value = $this->nodeFactory->createClassConstReference($formClass);
        }
        return $methodCall;
    }
}
