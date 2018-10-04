<?php declare(strict_types=1);

namespace Rector\Php\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PowToExpRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes pow(val, val2) to ** (exp) parameter',
            [new CodeSample('pow(1, 2);', '1**2;')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $funcCallNode
     */
    public function refactor(Node $funcCallNode): ?Node
    {
        if ((string) $funcCallNode->name !== 'pow') {
            return $funcCallNode;
        }

        if (count($funcCallNode->args) !== 2) {
            return $funcCallNode;
        }

        $firstArgument = $funcCallNode->args[0]->value;
        $secondArgument = $funcCallNode->args[1]->value;

        return new Pow($firstArgument, $secondArgument);
    }
}
