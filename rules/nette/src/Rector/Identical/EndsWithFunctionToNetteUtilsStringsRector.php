<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 * @see \Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector\EndsWithFunctionToNetteUtilsStringsRectorTest
 */
final class EndsWithFunctionToNetteUtilsStringsRector extends AbstractWithFunctionToNetteUtilsStringsRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use Nette\Utils\Strings::endsWith() over bare string-functions',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, -strlen($needle)) === $needle;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    public function getMethodName(): string
    {
        return 'endsWith';
    }

    public function matchContentAndNeedleOfSubstrOfVariableLength(
        Node $node,
        Variable $variable
    ): ?ContentExprAndNeedleExpr {
        if (! $this->isFuncCallName($node, 'substr')) {
            return null;
        }

        /** @var FuncCall $node */
        if (! $node->args[1]->value instanceof UnaryMinus) {
            return null;
        }

        /** @var UnaryMinus $unaryMinus */
        $unaryMinus = $node->args[1]->value;

        if (! $this->isFuncCallName($unaryMinus->expr, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $unaryMinus->expr;

        if ($this->areNodesEqual($strlenFuncCall->args[0]->value, $variable)) {
            return new ContentExprAndNeedleExpr($node->args[0]->value, $strlenFuncCall->args[0]->value);
        }

        return null;
    }
}
