<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
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

        // If it is an array, convert it to string
        if ($allowableTagsParam instanceof Array_) {
            // Replace the arg with a new one
            array_splice(
                $node->args,
                1,
                1,
                [
                    new Arg($this->getConvertArrayToStringFuncCall($allowableTagsParam)),
                ]
            );
            return $node;
        }

        // It is a variable, add logic to maybe convert to string
        return null;
    }

    /**
     * @param Array_ $allowableTagsParam
     */
    private function getConvertArrayToStringFuncCall($allowableTagsParam): Expr
    {
        return new Concat(
            new Concat(
                new String_('<'),
                new FuncCall(
                    new Name('implode'),
                    [
                        new Arg(new String_('><')),
                        new Arg($allowableTagsParam)
                    ]
                )
            ),
            new String_('>')
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

        $allowableTagsParam = $node->args[1]->value;
        return $allowableTagsParam instanceof Array_;
    }
}
