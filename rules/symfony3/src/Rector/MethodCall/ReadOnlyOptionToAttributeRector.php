<?php

declare(strict_types=1);

namespace Rector\Symfony3\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony3\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony3\NodeAnalyzer\FormOptionsArrayMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony3\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector\ReadOnlyOptionToAttributeRectorTest
 */
final class ReadOnlyOptionToAttributeRector extends AbstractRector
{
    /**
     * @var ArrayManipulator
     */
    private $arrayManipulator;

    /**
     * @var FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;

    /**
     * @var FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;

    public function __construct(
        ArrayManipulator $arrayManipulator,
        FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer,
        FormOptionsArrayMatcher $formOptionsArrayMatcher
    ) {
        $this->arrayManipulator = $arrayManipulator;
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change "read_only" option in form to attribute',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['read_only' => true]);
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return array<class-string<Node>>
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
        if (! $this->formAddMethodCallAnalyzer->matches($node)) {
            return null;
        }

        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (! $optionsArray instanceof Array_) {
            return null;
        }

        $readOnlyArrayItem = $this->arrayManipulator->findItemInInArrayByKeyAndUnset($optionsArray, 'read_only');
        if (! $readOnlyArrayItem instanceof ArrayItem) {
            return null;
        }

        // rename string
        $readOnlyArrayItem->key = new String_('readonly');

        $this->arrayManipulator->addItemToArrayUnderKey($optionsArray, $readOnlyArrayItem, 'attr');

        return $node;
    }
}
