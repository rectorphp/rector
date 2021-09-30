<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\FuncCall\MultiDirnameRector\MultiDirnameRectorTest
 */
final class MultiDirnameRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const DIRNAME = 'dirname';
    /**
     * @var int
     */
    private $nestingLevel = 0;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes multiple dirname() calls to one with nesting level', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('dirname(dirname($path));', 'dirname($path, 2);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->nestingLevel = 0;
        if (!$this->isName($node, self::DIRNAME)) {
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
        $node->args[1] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\LNumber($this->nestingLevel));
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DIRNAME_LEVELS;
    }
    private function matchNestedDirnameFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!$this->isName($funcCall, self::DIRNAME)) {
            return null;
        }
        $args = $funcCall->args;
        if (\count($args) >= 3) {
            return null;
        }
        // dirname($path, <LEVEL>);
        if (\count($args) === 2) {
            if (!$args[1]->value instanceof \PhpParser\Node\Scalar\LNumber) {
                return null;
            }
            /** @var LNumber $levelNumber */
            $levelNumber = $args[1]->value;
            $this->nestingLevel += $levelNumber->value;
        } else {
            ++$this->nestingLevel;
        }
        $nestedFuncCallNode = $args[0]->value;
        if (!$nestedFuncCallNode instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if ($this->isName($nestedFuncCallNode, self::DIRNAME)) {
            return $nestedFuncCallNode;
        }
        return null;
    }
}
