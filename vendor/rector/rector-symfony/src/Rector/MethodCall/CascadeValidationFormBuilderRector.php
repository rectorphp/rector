<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\CascadeValidationFormBuilderRectorTest
 *
 * @changelog https://gist.github.com/mickaelandrieu/5d27a2ffafcbdd64912f549aaf2a6df9#stuck-with-forms
 * @changelog https://stackoverflow.com/questions/39758392/symfony-3-validation-groups-inside-child-entity-ignored
 */
final class CascadeValidationFormBuilderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ArrayManipulator
     */
    private $arrayManipulator;
    public function __construct(ArrayManipulator $arrayManipulator)
    {
        $this->arrayManipulator = $arrayManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change "cascade_validation" option to specific node attribute', [new CodeSample(<<<'CODE_SAMPLE'
class SomeController
{
    public function someMethod()
    {
        $form = $this->createFormBuilder($article, ['cascade_validation' => true])
            ->add('author', new AuthorType())
            ->getForm();
    }

    protected function createFormBuilder()
    {
        return new FormBuilder();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeController
{
    public function someMethod()
    {
        $form = $this->createFormBuilder($article)
            ->add('author', new AuthorType(), [
                'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
            ])
            ->getForm();
    }

    protected function createFormBuilder()
    {
        return new FormBuilder();
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
        return [MethodCall::class, ArrayItem::class];
    }
    /**
     * @param MethodCall|ArrayItem $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ArrayItem) {
            return $this->refactorArrayItem($node);
        }
        if ($this->shouldSkipMethodCall($node)) {
            return null;
        }
        /** @var Array_ $formBuilderOptionsArrayNode */
        $formBuilderOptionsArrayNode = $node->getArgs()[1]->value;
        if (!$this->isSuccessfulRemovalCascadeValidationOption($node, $formBuilderOptionsArrayNode)) {
            return null;
        }
        $this->addConstraintsOptionToFollowingAddMethodCalls($node);
        return $node;
    }
    private function shouldSkipMethodCall(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'createFormBuilder')) {
            return \true;
        }
        if (!isset($methodCall->getArgs()[1])) {
            return \true;
        }
        $secondArg = $methodCall->getArgs()[1];
        return !$secondArg->value instanceof Array_;
    }
    private function isSuccessfulRemovalCascadeValidationOption(MethodCall $methodCall, Array_ $optionsArrayNode) : bool
    {
        foreach ($optionsArrayNode->items as $key => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$this->arrayManipulator->hasKeyName($arrayItem, 'cascade_validation')) {
                continue;
            }
            if (!$this->valueResolver->isTrue($arrayItem->value)) {
                continue;
            }
            unset($optionsArrayNode->items[$key]);
            // remove empty array
            if ($optionsArrayNode->items === []) {
                unset($methodCall->args[1]);
            }
            return \true;
        }
        return \false;
    }
    private function addConstraintsOptionToFollowingAddMethodCalls(MethodCall $methodCall) : void
    {
        $array = $this->createValidConstraintsArray();
        $constraintsArrayItem = new ArrayItem($array, new String_('constraints'));
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode instanceof MethodCall) {
            if ($this->isName($parentNode->name, 'add')) {
                /** @var Array_ $addOptionsArrayNode */
                $addOptionsArrayNode = isset($parentNode->getArgs()[2]) ? $parentNode->getArgs()[2]->value : new Array_();
                $addOptionsArrayNode->items[] = $constraintsArrayItem;
                $parentNode->args[2] = new Arg($addOptionsArrayNode);
            }
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
    }
    private function createValidConstraintsArray() : Array_
    {
        $new = new New_(new FullyQualified('Symfony\\Component\\Validator\\Constraints\\Valid'));
        return new Array_([new ArrayItem($new)]);
    }
    private function refactorArrayItem(ArrayItem $arrayItem) : ?\PhpParser\Node\Expr\ArrayItem
    {
        if (!$arrayItem->key instanceof Expr) {
            return null;
        }
        if (!$arrayItem->key instanceof String_) {
            return null;
        }
        if ($arrayItem->key->value !== 'cascade_validation') {
            return null;
        }
        if (!$this->valueResolver->isValue($arrayItem->value, \true)) {
            return null;
        }
        $arrayItem->key = new String_('constraints');
        $arrayItem->value = $this->createValidConstraintsArray();
        return $arrayItem;
    }
}
