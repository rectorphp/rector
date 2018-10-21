<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReadOnlyOptionToAttributeRector extends AbstractRector
{
    /**
     * @var string
     */
    private $formBuilderType;

    public function __construct(string $formBuilderType = 'Symfony\Component\Form\FormBuilderInterface')
    {
        $this->formBuilderType = $formBuilderType;
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

        $readonlyItem = $this->findReadonlyInOptions($optionsNode);
        if ($readonlyItem === null) {
            return null;
        }

        $this->addReadonlyItemToOptions($optionsNode, $readonlyItem);

        return $node;
    }

    private function isKeyWithName(ArrayItem $arrayItemNode, string $name): bool
    {
        return $arrayItemNode->key instanceof String_ && $arrayItemNode->key->value === $name;
    }

    private function findReadonlyInOptions(Array_ $optionsArrayNode): ?ArrayItem
    {
        foreach ($optionsArrayNode->items as $key => $item) {
            if (! $this->isKeyWithName($item, 'read_only')) {
                continue;
            }

            // rename string
            $item->key = new String_('readonly');

            // remove + recount for the printer
            unset($optionsArrayNode->items[$key]);
            $optionsArrayNode->items = array_values($optionsArrayNode->items);

            return $item;
        }

        return null;
    }

    private function addReadonlyItemToOptions(Array_ $optionsArrayNode, ArrayItem $readonlyArrayItem): void
    {
        foreach ($optionsArrayNode->items as $item) {
            if ($this->isKeyWithName($item, 'attr')) {
                if (! $item->value instanceof Array_) {
                    continue;
                }

                $item->value->items[] = $readonlyArrayItem;
                return;
            }
        }

        $optionsArrayNode->items[] = new ArrayItem(new Array_([$readonlyArrayItem]), new String_('attr'));
    }
}
