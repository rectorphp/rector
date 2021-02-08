<?php

declare(strict_types=1);

namespace Rector\Symfony3\Rector\MethodCall;

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
 * @see \Rector\Symfony3\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\CascadeValidationFormBuilderRectorTest
 */
final class CascadeValidationFormBuilderRector extends AbstractRector
{
    /**
     * @var ArrayManipulator
     */
    private $arrayManipulator;

    public function __construct(ArrayManipulator $arrayManipulator)
    {
        $this->arrayManipulator = $arrayManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change "cascade_validation" option to specific node attribute',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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

        /** @var Array_ $formBuilderOptionsArrayNode */
        $formBuilderOptionsArrayNode = $node->args[1]->value;

        if (! $this->isSuccessfulRemovalCascadeValidationOption($node, $formBuilderOptionsArrayNode)) {
            return null;
        }

        $this->addConstraintsOptionToFollowingAddMethodCalls($node);

        return $node;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->isName($methodCall->name, 'createFormBuilder')) {
            return true;
        }

        if (! isset($methodCall->args[1])) {
            return true;
        }

        return ! $methodCall->args[1]->value instanceof Array_;
    }

    private function isSuccessfulRemovalCascadeValidationOption(MethodCall $methodCall, Array_ $optionsArrayNode): bool
    {
        foreach ($optionsArrayNode->items as $key => $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }

            if (! $this->arrayManipulator->hasKeyName($arrayItem, 'cascade_validation')) {
                continue;
            }

            if (! $this->valueResolver->isTrue($arrayItem->value)) {
                continue;
            }

            unset($optionsArrayNode->items[$key]);

            // remove empty array
            if ($optionsArrayNode->items === []) {
                unset($methodCall->args[1]);
            }

            return true;
        }

        return false;
    }

    private function addConstraintsOptionToFollowingAddMethodCalls(MethodCall $methodCall): void
    {
        $new = new New_(new FullyQualified('Symfony\Component\Validator\Constraints\Valid'));
        $constraintsArrayItem = new ArrayItem($new, new String_('constraints'));

        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

        while ($parentNode instanceof MethodCall) {
            if ($this->isName($parentNode->name, 'add')) {
                /** @var Array_ $addOptionsArrayNode */
                $addOptionsArrayNode = isset($parentNode->args[2]) ? $parentNode->args[2]->value : new Array_();
                $addOptionsArrayNode->items[] = $constraintsArrayItem;

                $parentNode->args[2] = new Arg($addOptionsArrayNode);
            }

            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
    }
}
