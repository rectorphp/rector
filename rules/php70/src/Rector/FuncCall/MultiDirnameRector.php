<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector\MultiDirnameRectorTest
 */
final class MultiDirnameRector extends AbstractRector
{
    /**
     * @var string
     */
    private const DIRNAME = 'dirname';

    /**
     * @var int
     */
    private $nestingLevel = 0;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::DIRNAME_LEVELS)) {
            return null;
        }

        $this->nestingLevel = 0;

        if (! $this->isName($node, self::DIRNAME)) {
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

    private function matchNestedDirnameFuncCall(FuncCall $funcCall): ?FuncCall
    {
        if (! $this->isName($funcCall, self::DIRNAME)) {
            return null;
        }

        if (count($funcCall->args) >= 3) {
            return null;
        }

        // dirname($path, <LEVEL>);
        if (count($funcCall->args) === 2) {
            if (! $funcCall->args[1]->value instanceof LNumber) {
                return null;
            }

            /** @var LNumber $levelNumber */
            $levelNumber = $funcCall->args[1]->value;

            $this->nestingLevel += $levelNumber->value;
        } else {
            ++$this->nestingLevel;
        }

        $nestedFuncCallNode = $funcCall->args[0]->value;
        if (! $nestedFuncCallNode instanceof FuncCall) {
            return null;
        }

        if ($this->isName($nestedFuncCallNode, self::DIRNAME)) {
            return $nestedFuncCallNode;
        }

        return null;
    }
}
