<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
 * @see https://gist.github.com/mickaelandrieu/5d27a2ffafcbdd64912f549aaf2a6df9#stuck-with-forms
 * @see \Rector\Symfony\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\CascadeValidationFormBuilderRectorTest
 */
final class CascadeValidationFormBuilderRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ArrayManipulator
     */
    private $arrayManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ArrayManipulator $arrayManipulator)
    {
        $this->arrayManipulator = $arrayManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change "cascade_validation" option to specific node attribute', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var Array_ $formBuilderOptionsArrayNode */
        $formBuilderOptionsArrayNode = $node->args[1]->value;
        if (!$this->isSuccessfulRemovalCascadeValidationOption($node, $formBuilderOptionsArrayNode)) {
            return null;
        }
        $this->addConstraintsOptionToFollowingAddMethodCalls($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'createFormBuilder')) {
            return \true;
        }
        if (!isset($methodCall->args[1])) {
            return \true;
        }
        return !$methodCall->args[1]->value instanceof \PhpParser\Node\Expr\Array_;
    }
    private function isSuccessfulRemovalCascadeValidationOption(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Array_ $optionsArrayNode) : bool
    {
        foreach ($optionsArrayNode->items as $key => $arrayItem) {
            if ($arrayItem === null) {
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
    private function addConstraintsOptionToFollowingAddMethodCalls(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $new = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Validator\\Constraints\\Valid'));
        $constraintsArrayItem = new \PhpParser\Node\Expr\ArrayItem($new, new \PhpParser\Node\Scalar\String_('constraints'));
        $parentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        while ($parentNode instanceof \PhpParser\Node\Expr\MethodCall) {
            if ($this->isName($parentNode->name, 'add')) {
                /** @var Array_ $addOptionsArrayNode */
                $addOptionsArrayNode = isset($parentNode->args[2]) ? $parentNode->args[2]->value : new \PhpParser\Node\Expr\Array_();
                $addOptionsArrayNode->items[] = $constraintsArrayItem;
                $parentNode->args[2] = new \PhpParser\Node\Arg($addOptionsArrayNode);
            }
            $parentNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        }
    }
}
