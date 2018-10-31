<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class OptionNameRector extends AbstractRector
{
    /**
     * @var string
     */
    private $formBuilderType;

    /**
     * @var string[]
     */
    private $oldToNewOption = [
        'precision' => 'scale',
        'virtual' => 'inherit_data',
    ];

    public function __construct(string $formBuilderType = 'Symfony\Component\Form\FormBuilderInterface')
    {
        $this->formBuilderType = $formBuilderType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old option names to new ones in FormTypes in Form in Symfony', [
            new CodeSample(
<<<'CODE_SAMPLE'
$builder = new FormBuilder;
$builder->add("...", ["precision" => "...", "virtual" => "..."];
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$builder = new FormBuilder;
$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
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

        /** @var ArrayItem[] $optionsNodes */
        foreach ($optionsNode->items as $arrayItemNode) {
            if (! $arrayItemNode->key instanceof String_) {
                continue;
            }

            $this->processStringKey($arrayItemNode->key);
        }

        return $node;
    }

    private function processStringKey(String_ $stringKeyNode): void
    {
        $currentOptionName = $stringKeyNode->value;

        foreach ($this->oldToNewOption as $oldOption => $newOption) {
            if ($currentOptionName === $oldOption) {
                $stringKeyNode->value = $newOption;
            }
        }
    }
}
