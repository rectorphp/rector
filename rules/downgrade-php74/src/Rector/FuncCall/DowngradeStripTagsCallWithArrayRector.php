<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\DowngradeStripTagsCallWithArrayRectorTest
 */
final class DowngradeStripTagsCallWithArrayRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert 2nd param to `strip_tags` from array to string', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);

        // Function/method call: if array, change to string
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

        // Function/method call: if array, change to string
        strip_tags($string, is_array($tags = getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
        strip_tags($string, is_array($tags = $this->getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
    }
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->shouldRefactor($node)) {
            return null;
        }

        $allowableTagsParam = $node->args[1]->value;

        if ($allowableTagsParam instanceof Array_) {
            // If it is an array, convert it to string
            $newExpr = $this->getConvertArrayToStringFuncCall($allowableTagsParam);
        } elseif ($allowableTagsParam instanceof Variable) {
            // If it is a variable, add logic to maybe convert to string
            $newExpr = $this->getMaybeConvertArrayToStringFuncCall($allowableTagsParam);
        } else {
            // It is a function or method call: assign the value to a variable,
            // and apply same case as above
            $newExpr = $this->getMaybeConvertArrayToStringFuncCall($allowableTagsParam);
        }

        // Replace the arg with a new one
        array_splice($node->args, 1, 1, [new Arg($newExpr)]);
        return $node;
    }

    private function getConvertArrayToStringFuncCall(Expr $allowableTagsParam): Expr
    {
        return new Concat(
            new Concat(
                new String_('<'),
                new FuncCall(new Name('implode'), [new String_('><'), $allowableTagsParam])
            ),
            new String_('>')
        );
    }

    private function getMaybeConvertArrayToStringFuncCall(Expr $allowableTagsParam): Expr
    {
        return new Ternary(
            new FuncCall(new Name('is_array'), [$allowableTagsParam]),
            $this->getConvertArrayToStringFuncCall($allowableTagsParam),
            $allowableTagsParam
        );
    }

    /**
     * @param FuncCall $node
     */
    private function shouldRefactor(Node $node): bool
    {
        if (! $this->isFuncCallName($node, 'strip_tags')) {
            return false;
        }

        // If param not provided, do nothing
        if (count($node->args) < 2) {
            return false;
        }

        // Process anything other than String and null (eg: variables, function calls)
        $allowableTagsParam = $node->args[1]->value;
        return $allowableTagsParam instanceof Array_ || $allowableTagsParam instanceof Variable || $allowableTagsParam instanceof FuncCall || $allowableTagsParam instanceof MethodCall;
    }
}
