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

    private function matchNestedDirnameFuncCall(Node $node): ?FuncCall
    {
        if (! $this->isName($node, 'dirname')) {
            return null;
        }

        /** @var FuncCall $node */
        if (count($node->args) >= 3) {
            return null;
        }

        // dirname($path, <LEVEL>);
        if (count($node->args) === 2) {
            if (! $node->args[1]->value instanceof LNumber) {
                return null;
            }

            /** @var LNumber $levelNumber */
            $levelNumber = $node->args[1]->value;

            $this->nestingLevel += $levelNumber->value;
        } else {
            ++$this->nestingLevel;
        }

        if ($this->isName($node->args[0]->value, 'dirname')) {
            return $node->args[0]->value;
        }

        return null;
    }
}
