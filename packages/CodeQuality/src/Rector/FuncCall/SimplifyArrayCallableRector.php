<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyArrayCallableRector extends AbstractRector
{
    /**
     * @var string|null
     */
    private $activeFuncCallName;

    /**
     * @var int[]
     */
    private $functionsWithCallableArgumentPosition = [
        'array_filter' => 1,
        'array_map' => 0,
        'array_walk' => 1,
        'array_reduce' => 1,
        'usort' => 1,
        'uksort' => 1,
        'uasort' => 1,
        'array_walk_recursive' => 1,
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes redundant anonymous bool functions to simple calls', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$paths = array_filter($paths, function ($path): bool {
    return is_dir($path);
});
CODE_SAMPLE
                ,
                'array_filter($paths, "is_dir");'
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
        foreach ($this->functionsWithCallableArgumentPosition as $function => $callablePosition) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            if (! $node->args[$callablePosition]->value instanceof Closure) {
                continue;
            }

            /** @var Closure $closureNode */
            $closureNode = $node->args[$callablePosition]->value;
            if ($this->isUsefulClosure($closureNode)) {
                return null;
            }

            $node->args[$callablePosition] = new Arg(new String_($this->activeFuncCallName));

            return $node;
        }

        return $node;
    }

    private function isUsefulClosure(Closure $closureNode): bool
    {
        // too complicated
        if (! $closureNode->stmts[0] instanceof Return_) {
            return true;
        }

        /** @var Return_ $returnNode */
        $returnNode = $closureNode->stmts[0];
        if (! $returnNode->expr instanceof FuncCall) {
            return true;
        }

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $returnNode->expr;

        $this->activeFuncCallName = $this->getName($funcCallNode);

        return ! $this->areNodesEqual($closureNode->params, $returnNode->expr->args);
    }
}
