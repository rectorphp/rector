<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/functions.arrow.php
 *
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
        // Strings: do no change
        strip_tags($string, '<a><p>');

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
        // Strings: do no change
        strip_tags($string, '<a><p>');

        // Arrays: change to string
        strip_tags($string, '<' . (implode('><', ['a', 'p']) . '>'));

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
        if (! $this->isFuncCallName($node, 'strip_tags')) {
            return null;
        }

        $allowableTagsParam = $node->args[1]->value;

        // If it is a String, then do nothing
        if ($allowableTagsParam instanceof String_) {
            return null;
        }

        // If it is an array, convert it to string
        if ($allowableTagsParam instanceof Array_) {
            // Replace the arg with a new one
            array_splice(
                $node->args,
                1,
                1,
                [
                    new Arg(
                        new Concat(
                            new String_('<'),
                            new Concat(
                                new FuncCall(
                                    new Name('implode'),
                                    [
                                        new Arg(new String_('><')),
                                        new Arg($allowableTagsParam)
                                    ]
                                ),
                                new String_('>')
                            )
                        )
                    ),
                ]
            );
            return $node;
        }

        return null;

        // dump($allowableTagsParam);die;

        /** @var Assign|Node|null $previousAssignArraysKeysFuncCall */
        $previousAssignArraysKeysFuncCall = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use (
            $allowableTagsParam
        ): bool {
            // breaking out of scope
            if ($node instanceof FunctionLike) {
                return true;
            }

            if (! $node instanceof Assign) {
                return false;
            }

            if (! $this->areNodesEqual($allowableTagsParam, $node->var)) {
                return false;
            }

            return $this->isFuncCallName($node->expr, 'array_keys');
        });

        if (! $previousAssignArraysKeysFuncCall instanceof Assign) {
            return null;
        }

        /** @var FuncCall $arrayKeysFuncCall */
        $arrayKeysFuncCall = $previousAssignArraysKeysFuncCall->expr;

        $this->removeNode($previousAssignArraysKeysFuncCall);

        return $this->createArrayKeyExists($node, $arrayKeysFuncCall);
    }

    private function createArrayKeyExists(FuncCall $inArrayFuncCall, FuncCall $arrayKeysFuncCall): FuncCall
    {
        $arguments = [$inArrayFuncCall->args[0], $arrayKeysFuncCall->args[0]];

        return new FuncCall(new Name('array_key_exists'), $arguments);
    }
}
