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
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\ReplaceEachAssignmentWithKeyCurrentRectorTest
 */
final class ReplaceEachAssignmentWithKeyCurrentRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const KEY = 'key';
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(NodesToAddCollector $nodesToAddCollector)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_EACH_OUTSIDE_LOOP;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace each() assign outside loop', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $node->expr;
        if (!isset($eachFuncCall->args[0])) {
            return null;
        }
        if (!$eachFuncCall->args[0] instanceof Arg) {
            return null;
        }
        $eachedVariable = $eachFuncCall->args[0]->value;
        $assignVariable = $node->var;
        $newNodes = $this->createNewNodes($assignVariable, $eachedVariable);
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $node);
        $this->removeNode($node);
        return null;
    }
    private function shouldSkip(Assign $assign) : bool
    {
        if (!$assign->expr instanceof FuncCall) {
            return \true;
        }
        if (!$this->nodeNameResolver->isName($assign->expr, 'each')) {
            return \true;
        }
        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof While_) {
            return \true;
        }
        // skip assign to List
        if (!$parentNode instanceof Assign) {
            return \false;
        }
        return $parentNode->var instanceof List_;
    }
    /**
     * @return array<Assign|FuncCall>
     */
    private function createNewNodes(Expr $assignVariable, Expr $eachedVariable) : array
    {
        return [$this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 1, 'current'), $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 'value', 'current'), $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 0, self::KEY), $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, self::KEY, self::KEY), $this->nodeFactory->createFuncCall('next', [new Arg($eachedVariable)])];
    }
    /**
     * @param string|int $dimValue
     */
    private function createDimFetchAssignWithFuncCall(Expr $assignVariable, Expr $eachedVariable, $dimValue, string $functionName) : Assign
    {
        $dimExpr = BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new ArrayDimFetch($assignVariable, $dimExpr);
        return new Assign($arrayDimFetch, $this->nodeFactory->createFuncCall($functionName, [new Arg($eachedVariable)]));
    }
}
