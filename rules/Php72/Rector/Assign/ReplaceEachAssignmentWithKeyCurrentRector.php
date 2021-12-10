<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\Assign;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\ReplaceEachAssignmentWithKeyCurrentRectorTest
 */
final class ReplaceEachAssignmentWithKeyCurrentRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const KEY = 'key';
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NO_EACH_OUTSIDE_LOOP;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace each() assign outside loop', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];
$eachedArray = each($array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];
$eachedArray[1] = current($array);
$eachedArray['value'] = current($array);
$eachedArray[0] = key($array);
$eachedArray['key'] = key($array);
next($array);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $node->expr;
        if (!isset($eachFuncCall->args[0])) {
            return null;
        }
        if (!$eachFuncCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $eachedVariable = $eachFuncCall->args[0]->value;
        $assignVariable = $node->var;
        $newNodes = $this->createNewNodes($assignVariable, $eachedVariable);
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $node);
        $this->removeNode($node);
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        if (!$assign->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return \true;
        }
        if (!$this->nodeNameResolver->isName($assign->expr, 'each')) {
            return \true;
        }
        $parentNode = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Stmt\While_) {
            return \true;
        }
        // skip assign to List
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        return $parentNode->var instanceof \PhpParser\Node\Expr\List_;
    }
    /**
     * @return array<int, Assign|FuncCall>
     */
    private function createNewNodes(\PhpParser\Node\Expr $assignVariable, \PhpParser\Node\Expr $eachedVariable) : array
    {
        $newNodes = [];
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 1, 'current');
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 'value', 'current');
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 0, self::KEY);
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, self::KEY, self::KEY);
        $newNodes[] = $this->nodeFactory->createFuncCall('next', [new \PhpParser\Node\Arg($eachedVariable)]);
        return $newNodes;
    }
    /**
     * @param int|string $dimValue
     */
    private function createDimFetchAssignWithFuncCall(\PhpParser\Node\Expr $assignVariable, \PhpParser\Node\Expr $eachedVariable, $dimValue, string $functionName) : \PhpParser\Node\Expr\Assign
    {
        $dim = \PhpParser\BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($assignVariable, $dim);
        return new \PhpParser\Node\Expr\Assign($arrayDimFetch, $this->nodeFactory->createFuncCall($functionName, [new \PhpParser\Node\Arg($eachedVariable)]));
    }
}
