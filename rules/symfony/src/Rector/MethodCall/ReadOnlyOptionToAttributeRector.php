<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\Manipulator\ArrayManipulator;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector\ReadOnlyOptionToAttributeRectorTest
 */
final class ReadOnlyOptionToAttributeRector extends AbstractFormAddRector
{
    /**
     * @var ArrayManipulator
     */
    private $arrayManipulator;

    public function __construct(ArrayManipulator $arrayManipulator)
    {
        $this->arrayManipulator = $arrayManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change "read_only" option in form to attribute', [
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

        $optionsArray = $this->matchOptionsArray($node);
        if ($optionsArray === null) {
            return null;
        }
        if (! $optionsArray instanceof Array_) {
            return null;
        }

        $readOnlyArrayItem = $this->arrayManipulator->findItemInInArrayByKeyAndUnset($optionsArray, 'read_only');
        if ($readOnlyArrayItem === null) {
            return null;
        }

        // rename string
        $readOnlyArrayItem->key = new String_('readonly');

        $this->arrayManipulator->addItemToArrayUnderKey($optionsArray, $readOnlyArrayItem, 'attr');

        return $node;
    }
}
