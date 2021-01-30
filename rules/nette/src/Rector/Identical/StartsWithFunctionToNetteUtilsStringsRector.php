<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 *
 * @see \Rector\Nette\Tests\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector\StartsWithFunctionToNetteUtilsStringsRectorTest
 */
final class StartsWithFunctionToNetteUtilsStringsRector extends AbstractWithFunctionToNetteUtilsStringsRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use Nette\Utils\Strings::startsWith() over bare string-functions',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, 0, strlen($needle)) === $needle;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::startsWith($content, $needle);
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    public function getMethodName(): string
    {
        return 'startsWith';
    }

    public function matchContentAndNeedleOfSubstrOfVariableLength(
        Node $node,
        Variable $variable
    ): ?ContentExprAndNeedleExpr {
        if (! $this->isFuncCallName($node, 'substr')) {
            return null;
        }

        /** @var FuncCall $node */
        if (! $this->valueResolver->isValue($node->args[1]->value, 0)) {
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
            return new ContentExprAndNeedleExpr($node->args[0]->value, $strlenFuncCall->args[0]->value);
        }

        return null;
    }
}
