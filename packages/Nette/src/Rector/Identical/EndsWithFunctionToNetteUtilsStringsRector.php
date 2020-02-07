<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 * @see \Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector\EndsWithFunctionToNetteUtilsStringsRectorTest
 */
final class EndsWithFunctionToNetteUtilsStringsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare string-functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, -strlen($needle)) === $needle;
        $no = $needle !== substr($content, -strlen($needle));
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
        $no = !\Nette\Utils\Strings::endsWith($content, $needle);
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
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $contentAndNeedle = null;

        if ($node->left instanceof Variable) {
            $contentAndNeedle = $this->matchContentAndNeedleOfSubstrOfVariableLength($node->right, $node->left);
        }

        if ($node->right instanceof Variable) {
            $contentAndNeedle = $this->matchContentAndNeedleOfSubstrOfVariableLength($node->left, $node->right);
        }

        if ($contentAndNeedle === null) {
            return null;
        }

        [$contentNode, $needleNode] = $contentAndNeedle;

        // starts with
        $startsWithStaticCall = $this->createStaticCall('Nette\Utils\Strings', 'endsWith', [
            $contentNode,
            $needleNode,
        ]);

        if ($node instanceof NotIdentical) {
            return new BooleanNot($startsWithStaticCall);
        }

        return $startsWithStaticCall;
    }

    /**
     * @return Expr[]|null
     */
    private function matchContentAndNeedleOfSubstrOfVariableLength(Node $node, Variable $variable): ?array
    {
        if (! $node instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($node, 'substr')) {
            return null;
        }

        if (! $node->args[1]->value instanceof UnaryMinus) {
            return null;
        }

        /** @var UnaryMinus $unaryMinus */
        $unaryMinus = $node->args[1]->value;

        if (! $unaryMinus->expr instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($unaryMinus->expr, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $unaryMinus->expr;

        if ($this->areNodesEqual($strlenFuncCall->args[0]->value, $variable)) {
            return [$node->args[0]->value, $strlenFuncCall->args[0]->value];
        }

        return null;
    }
}
