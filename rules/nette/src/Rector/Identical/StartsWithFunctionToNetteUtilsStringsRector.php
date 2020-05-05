<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 * @see \Rector\Nette\Tests\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector\StartsWithFunctionToNetteUtilsStringsRectorTest
 */
final class StartsWithFunctionToNetteUtilsStringsRector extends AbstractWithFunctionToNetteUtilsStringsRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings::startsWith() over bare string-functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, 0, strlen($needle)) === $needle;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::startsWith($content, $needle);
    }
}
PHP
            ),
        ]);
    }

    protected function getMethodName(): string
    {
        return 'startsWith';
    }

    /**
     * @return Expr[]|null
     */
    protected function matchContentAndNeedleOfSubstrOfVariableLength(Node $node, Variable $variable): ?array
    {
        if (! $this->isFuncCallName($node, 'substr')) {
            return null;
        }

        /** @var FuncCall $node */
        if (! $this->isValue($node->args[1]->value, 0)) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        if (! $node->args[2]->value instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($node->args[2]->value, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $node->args[2]->value;
        if ($this->areNodesEqual($strlenFuncCall->args[0]->value, $variable)) {
            return [$node->args[0]->value, $strlenFuncCall->args[0]->value];
        }

        return null;
    }
}
