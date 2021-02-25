<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\WrapEncapsedVariableInCurlyBracesRectorTest
 */
final class WrapEncapsedVariableInCurlyBracesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap encapsed variables in curly braces', [
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

        foreach ($node->parts as $index => $part) {
            if ($part instanceof Variable) {
                $previousNode = $part->getAttribute(AttributeKey::PREVIOUS_NODE);
                $previousNodeEndTokenPosition = $previousNode instanceof Node ? $previousNode->getEndTokenPos() : $startTokenPos;

                if ($previousNodeEndTokenPosition + 1 === $part->getStartTokenPos()) {
                    $hasVariableBeenWrapped = true;

                    $node->parts[$index] = new Variable($part->name);
                }
            }
        }

        if (! $hasVariableBeenWrapped) {
            return null;
        }

        return $node;
    }
}
