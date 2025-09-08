<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Token;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\WrapEncapsedVariableInCurlyBracesRectorTest
 */
final class WrapEncapsedVariableInCurlyBracesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [InterpolatedString::class];
    }
    /**
     * @param InterpolatedString $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasVariableBeenWrapped = \false;
        $oldTokens = $this->file->getOldTokens();
        foreach ($node->parts as $index => $nodePart) {
            if ($nodePart instanceof Variable && $nodePart->getStartTokenPos() >= 0) {
                $start = $oldTokens[$nodePart->getStartTokenPos() - 1] ?? null;
                $end = $oldTokens[$nodePart->getEndTokenPos() + 1] ?? null;
                if ($start instanceof Token && $end instanceof Token && $start->text === '{' && $end->text === '}') {
                    continue;
                }
                $hasVariableBeenWrapped = \true;
                $node->parts[$index] = new Variable($nodePart->name);
            }
        }
        if (!$hasVariableBeenWrapped) {
            return null;
        }
        return $node;
    }
}
