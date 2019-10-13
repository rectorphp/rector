<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Manipulator\ArrayManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector\ReadOnlyOptionToAttributeRectorTest
 */
final class ReadOnlyOptionToAttributeRector extends AbstractRector
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
                <<<'PHP'
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['read_only' => true]);
}
PHP
                ,
                <<<'PHP'
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
}
PHP
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
        if (! $this->isName($node->name, 'add')) {
            return null;
        }

        if (! $this->isObjectType($node->var, FormBuilderInterface::class)) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        $optionsNode = $node->args[2]->value;
        if (! $optionsNode instanceof Array_) {
            return null;
        }

        $readonlyItem = $this->arrayManipulator->findItemInInArrayByKeyAndUnset($optionsNode, 'read_only');
        if ($readonlyItem === null) {
            return null;
        }

        // rename string
        $readonlyItem->key = new String_('readonly');

        $this->arrayManipulator->addItemToArrayUnderKey($optionsNode, $readonlyItem, 'attr');

        return $node;
    }
}
