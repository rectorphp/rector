<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector\ReadOnlyOptionToAttributeRectorTest
 */
final class ReadOnlyOptionToAttributeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ArrayManipulator
     */
    private $arrayManipulator;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;
    public function __construct(\Rector\Core\NodeManipulator\ArrayManipulator $arrayManipulator, \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher $formOptionsArrayMatcher)
    {
        $this->arrayManipulator = $arrayManipulator;
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change "read_only" option in form to attribute', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['read_only' => true]);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
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
        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (!$optionsArray instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $readOnlyArrayItem = $this->arrayManipulator->findItemInInArrayByKeyAndUnset($optionsArray, 'read_only');
        if (!$readOnlyArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        // rename string
        $readOnlyArrayItem->key = new \PhpParser\Node\Scalar\String_('readonly');
        $this->arrayManipulator->addItemToArrayUnderKey($optionsArray, $readOnlyArrayItem, 'attr');
        return $node;
    }
}
