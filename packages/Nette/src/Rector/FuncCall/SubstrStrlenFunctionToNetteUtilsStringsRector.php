<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 * @see \Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector\SubstrStrlenFunctionToNetteUtilsStringsRectorTest
 */
final class SubstrStrlenFunctionToNetteUtilsStringsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $functionToStaticMethod = [
        'substr' => 'substring',
        'strlen' => 'length',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare string-functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return substr($value, 0, 3);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return \Nette\Utils\Strings::substring($value, 0, 3);
    }
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->functionToStaticMethod as $function => $staticMethod) {
            if ($this->isName($node, $function)) {
                return $this->createStaticCall('Nette\Utils\Strings', $staticMethod, $node->args);
            }
        }

        return null;
    }
}
