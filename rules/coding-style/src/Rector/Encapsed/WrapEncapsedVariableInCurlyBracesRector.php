<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\WrapEncapsedVariableInCurlyBracesRectorTest
 */
final class WrapEncapsedVariableInCurlyBracesRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Wrap encapsed variables in curly braces', [
            new CodeSample(
                <<<'CODE_SAMPLE'
function run($world)
{
    echo "Hello $world!"
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
function run($world)
{
    echo "Hello {$world}!"
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
        return [Encapsed::class];
    }

    /**
     * @param Encapsed $node
     */
    public function refactor(Node $node): ?Node
    {
        $startTokenPos = $node->getStartTokenPos();
        $hasVariableBeenWrapped = false;

        foreach ($node->parts as $index => $nodePart) {
            if ($nodePart instanceof Variable) {
                $previousNode = $nodePart->getAttribute(AttributeKey::PREVIOUS_NODE);
                $previousNodeEndTokenPosition = $previousNode === null ? $startTokenPos : $previousNode->getEndTokenPos();

                if ($previousNodeEndTokenPosition + 1 === $nodePart->getStartTokenPos()) {
                    $hasVariableBeenWrapped = true;

                    $node->parts[$index] = new Variable($nodePart->name);
                }
            }
        }

        if (! $hasVariableBeenWrapped) {
            return null;
        }

        return $node;
    }
}
