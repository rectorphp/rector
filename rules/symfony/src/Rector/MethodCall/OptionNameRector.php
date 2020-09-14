<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\OptionNameRector\OptionNameRectorTest
 */
final class OptionNameRector extends AbstractFormAddRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_OPTION = [
        'precision' => 'scale',
        'virtual' => 'inherit_data',
    ];

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
        if (! $this->isFormAddMethodCall($node)) {
            return null;
        }

        $optionsArray = $this->matchOptionsArray($node);
        if ($optionsArray === null) {
            return null;
        }

        foreach ($optionsArray->items as $arrayItemNode) {
            if ($arrayItemNode === null) {
                continue;
            }

            if (! $arrayItemNode->key instanceof String_) {
                continue;
            }

            $this->processStringKey($arrayItemNode->key);
        }

        return $node;
    }

    private function processStringKey(String_ $string): void
    {
        $currentOptionName = $string->value;

        $string->value = self::OLD_TO_NEW_OPTION[$currentOptionName] ?? $string->value;
    }
}
