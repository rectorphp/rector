<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Manipulator\ArrayManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReadOnlyOptionToAttributeRector extends AbstractRector
{
    /**
     * @var string
     */
    private $formBuilderType;

    /**
     * @var ArrayManipulator
     */
    private $arrayManipulator;

    public function __construct(
        ArrayManipulator $arrayManipulator,
        string $formBuilderType = 'Symfony\Component\Form\FormBuilderInterface'
    ) {
        $this->formBuilderType = $formBuilderType;
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
    $builder->add('cuid', TextType::class, ['attr' => [read_only' => true]]);
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
        if (! $this->isName($node, 'add')) {
            return null;
        }

        if (! $this->isType($node, $this->formBuilderType)) {
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
