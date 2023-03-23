<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\WrapEncapsedVariableInCurlyBracesRectorTest
 */
final class WrapEncapsedVariableInCurlyBracesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Wrap encapsed variables in curly braces', [new CodeSample(<<<'CODE_SAMPLE'
function run($world)
{
    echo "Hello $world!";
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($world)
{
    echo "Hello {$world}!";
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Encapsed::class];
    }
    /**
     * @param Encapsed $node
     */
    public function refactor(Node $node) : ?Node
    {
        $startTokenPos = $node->getStartTokenPos();
        $hasVariableBeenWrapped = \false;
        foreach ($node->parts as $index => $nodePart) {
            if ($nodePart instanceof Variable) {
                $previousNode = $node->parts[$index - 1] ?? null;
                $previousNodeEndTokenPosition = $previousNode instanceof Node ? $previousNode->getEndTokenPos() : $startTokenPos;
                if ($previousNodeEndTokenPosition + 1 === $nodePart->getStartTokenPos()) {
                    $hasVariableBeenWrapped = \true;
                    $node->parts[$index] = new Variable($nodePart->name);
                }
            }
        }
        if (!$hasVariableBeenWrapped) {
            return null;
        }
        return $node;
    }
}
