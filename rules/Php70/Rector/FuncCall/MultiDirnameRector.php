<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\FuncCall\MultiDirnameRector\MultiDirnameRectorTest
 */
final class MultiDirnameRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const DIRNAME = 'dirname';
    private int $nestingLevel = 0;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes multiple dirname() calls to one with nesting level', [new CodeSample('dirname(dirname($path));', 'dirname($path, 2);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->nestingLevel = 0;
        if (!$this->isName($node, self::DIRNAME)) {
            return null;
        }
        $activeFuncCallNode = $node;
        $lastFuncCallNode = $node;
        $shouldUpdate = \false;
        while (($activeFuncCallNode = $this->matchNestedDirnameFuncCall($activeFuncCallNode)) instanceof FuncCall) {
            $lastFuncCallNode = $activeFuncCallNode;
            $shouldUpdate = \true;
        }
        // nothing to improve
        if (!$shouldUpdate || $this->shouldSkip()) {
            return null;
        }
        $node->args[0] = $lastFuncCallNode->args[0];
        $node->args[1] = new Arg(new Int_($this->nestingLevel));
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DIRNAME_LEVELS;
    }
    private function shouldSkip() : bool
    {
        return $this->nestingLevel < 2;
    }
    private function matchNestedDirnameFuncCall(FuncCall $funcCall) : ?FuncCall
    {
        if (!$this->isName($funcCall, self::DIRNAME)) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        $args = $funcCall->getArgs();
        if (\count($args) >= 3) {
            return null;
        }
        // dirname($path, <LEVEL>);
        if (\count($args) === 2) {
            if (!$args[1]->value instanceof Int_) {
                return null;
            }
            /** @var Int_ $levelNumber */
            $levelNumber = $args[1]->value;
            $this->nestingLevel += $levelNumber->value;
        } else {
            ++$this->nestingLevel;
        }
        $nestedFuncCallNode = $args[0]->value;
        if (!$nestedFuncCallNode instanceof FuncCall) {
            return null;
        }
        if ($this->isName($nestedFuncCallNode, self::DIRNAME)) {
            return $nestedFuncCallNode;
        }
        return null;
    }
}
