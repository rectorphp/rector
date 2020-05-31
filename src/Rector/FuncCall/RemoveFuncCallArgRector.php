<?php

declare(strict_types=1);

namespace Rector\Core\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://twitter.com/afilina for sponsoring this rule
 *
 * @see \Rector\Core\Tests\Rector\FuncCall\RemoveFuncCallArgRector\RemoveFuncCallArgRectorTest
 */
final class RemoveFuncCallArgRector extends AbstractRector
{
    /**
     * @var int[][]
     */
    private $argumentPositionByFunctionName = [];

    /**
     * @param int[][] $argumentPositionByFunctionName
     */
    public function __construct(array $argumentPositionByFunctionName = [])
    {
        $this->argumentPositionByFunctionName = $argumentPositionByFunctionName;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove argument by position by function name', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
remove_last_arg(1, 2);
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
remove_last_arg(1);
CODE_SAMPLE
                , [
                    '$argumentPositionByFunctionName' => [
                        'remove_last_arg' => [1],
                    ],
                ]),
        ]);
    }

    /**
     * @return class-string[]
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
        foreach ($this->argumentPositionByFunctionName as $functionName => $agumentPositions) {
            if (! $this->isName($node->name, $functionName)) {
                continue;
            }

            foreach (array_keys($node->args) as $position) {
                if (! in_array($position, $agumentPositions, true)) {
                    continue;
                }

                unset($node->args[$position]);
            }
        }

        return $node;
    }
}
