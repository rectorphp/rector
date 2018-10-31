<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\ArrayAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://gist.github.com/mickaelandrieu/5d27a2ffafcbdd64912f549aaf2a6df9#stuck-with-forms
 */
final class CascadeValidationFormBuilderRector extends AbstractRector
{
    /**
     * @var ArrayAnalyzer
     */
    private $arrayAnalyzer;

    public function __construct(ArrayAnalyzer $arrayAnalyzer)
    {
        $this->arrayAnalyzer = $arrayAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change "cascade_validation" option to specific node attribute', [
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

        if (! $this->findAndRemoveCascadeValidationOption($node, $formBuilderOptionsArrayNode)) {
            return null;
        }

        $this->addConstraintsOptionToFollowingAddMethodCalls($node);

        return $node;
    }

    private function shouldSkip(MethodCall $methodCallNode): bool
    {
        if (! $this->isName($methodCallNode, 'createFormBuilder')) {
            return true;
        }

        if (! isset($methodCallNode->args[1])) {
            return true;
        }

        return ! $methodCallNode->args[1]->value instanceof Array_;
    }

    private function findAndRemoveCascadeValidationOption(MethodCall $methodCallNode, Array_ $optionsArrayNode): bool
    {
        foreach ($optionsArrayNode->items as $key => $arrayItem) {
            if (! $this->arrayAnalyzer->hasKeyName($arrayItem, 'cascade_validation')) {
                continue;
            }

            if (! $this->isTrue($arrayItem->value)) {
                continue;
            }

            unset($optionsArrayNode->items[$key]);

            // remove empty array
            if (count($optionsArrayNode->items) === 0) {
                unset($methodCallNode->args[1]);
            } else {
                // recount indexes for printer
                $optionsArrayNode->items = array_values($optionsArrayNode->items);
            }

            return true;
        }

        return false;
    }

    private function addConstraintsOptionToFollowingAddMethodCalls(Node $node): void
    {
        $constraintsArrayItem = new ArrayItem(
            new New_(new FullyQualified('Symfony\Component\Validator\Constraints\Valid')),
            new String_('constraints')
        );

        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        while ($parentNode instanceof MethodCall) {
            if ($this->isName($parentNode, 'add')) {
                /** @var Array_ $addOptionsArrayNode */
                $addOptionsArrayNode = isset($parentNode->args[2]) ? $parentNode->args[2]->value : new Array_();
                $addOptionsArrayNode->items[] = $constraintsArrayItem;

                $parentNode->args[2] = new Arg($addOptionsArrayNode);
            }

            $parentNode = $parentNode->getAttribute(Attribute::PARENT_NODE);
        }
    }
}
