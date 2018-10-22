<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MultiDirnameRector extends AbstractRector
{
    /**
     * @var int
     */
    private $nestingLevel = 0;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes multiple dirname() calls to one with nesting level',
            [new CodeSample('dirname(dirname($path));', 'dirname($path, 2);')]
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
        $this->nestingLevel = 0;

        if (! $this->isName($node, 'dirname')) {
            return null;
        }

        $activeFuncCallNode = $node;
        $lastFuncCallNode = $node;

        while ($activeFuncCallNode = $this->matchNestedDirnameFuncCall($activeFuncCallNode)) {
            $lastFuncCallNode = $activeFuncCallNode;
        }

        // nothing to improve
        if ($this->nestingLevel < 2) {
            return $activeFuncCallNode;
        }

        $node->args[0] = $lastFuncCallNode->args[0];
        $node->args[1] = new Arg(new LNumber($this->nestingLevel));

        return $node;
    }

    private function matchNestedDirnameFuncCall(FuncCall $funcCallNode): ?FuncCall
    {
        if (! $this->isName($funcCallNode, 'dirname')) {
            return null;
        }

        if (count($funcCallNode->args) >= 3) {
            return null;
        }

        // dirname($path, <LEVEL>);
        if (count($funcCallNode->args) === 2) {
            if (! $funcCallNode->args[1]->value instanceof LNumber) {
                return null;
            }

            /** @var LNumber $levelNumber */
            $levelNumber = $funcCallNode->args[1]->value;

            $this->nestingLevel += $levelNumber->value;
        } else {
            ++$this->nestingLevel;
        }

        $nestedFuncCallNode = $funcCallNode->args[0]->value;
        if (! $nestedFuncCallNode instanceof FuncCall) {
            return null;
        }

        if ($this->isName($nestedFuncCallNode, 'dirname')) {
            return $nestedFuncCallNode;
        }

        return null;
    }
}
