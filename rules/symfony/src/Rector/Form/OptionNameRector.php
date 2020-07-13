<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Symfony\Rector\MethodCall\AbstractFormAddRector;

/**
 * @see \Rector\Symfony\Tests\Rector\Form\OptionNameRector\OptionNameRectorTest
 */
final class OptionNameRector extends AbstractFormAddRector
{
    /**
     * @var string[]
     */
    private const OLD_TO_NEW_OPTION = [
        'precision' => 'scale',
        'virtual' => 'inherit_data',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old option names to new ones in FormTypes in Form in Symfony', [
            new CodeSample(
<<<'PHP'
$builder = new FormBuilder;
$builder->add("...", ["precision" => "...", "virtual" => "..."];
PHP
                ,
<<<'PHP'
$builder = new FormBuilder;
$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
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
        if (! $this->isFormAddMethodCall($node)) {
            return null;
        }

        $optionsArray = $this->matchOptionsArray($node);
        if ($optionsArray === null) {
            return null;
        }

        foreach ($optionsArray->items as $arrayItemNode) {
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
