<?php declare(strict_types=1);

namespace Rector\Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\NodeAnalyzer\FuncCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyFuncGetArgsCountRector extends AbstractRector
{
    /**
     * @var FuncCallAnalyzer
     */
    private $funcCallAnalyzer;

    public function __construct(FuncCallAnalyzer $funcCallAnalyzer)
    {
        $this->funcCallAnalyzer = $funcCallAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify count of func_get_args() to fun_num_args()',
            [new CodeSample('count(func_get_args());', 'func_num_args();')]
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
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->funcCallAnalyzer->isName($node, 'count')) {
            return $node;
        }

        if (! $node->args[0]->value instanceof FuncCall) {
            return $node;
        }

        /** @var FuncCall $innerFuncCall */
        $innerFuncCall = $node->args[0]->value;

        if (! $this->funcCallAnalyzer->isName($innerFuncCall, 'func_get_args')) {
            return $node;
        }

        return new FuncCall(new Name('func_num_args'));
    }
}
