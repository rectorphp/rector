<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/deprecations_php_7_2#each
 *
 * @see \Rector\Tests\Php72\Rector\Assign\ListEachRector\ListEachRectorTest
 */
final class ListEachRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    public function __construct(AssignManipulator $assignManipulator)
    {
        $this->assignManipulator = $assignManipulator;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_EACH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('each() function is deprecated, use key() and current() instead', [new CodeSample(<<<'CODE_SAMPLE'
list($key, $callback) = each($callbacks);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$key = key($callbacks);
$callback = current($callbacks);
next($callbacks);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return null|Expression|Stmt[]
     */
    public function refactor(Node $node)
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if ($this->shouldSkipAssign($assign)) {
            return null;
        }
        /** @var List_ $listNode */
        $listNode = $assign->var;
        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $assign->expr;
        // only key: list($key, ) = each($values);
        if ($listNode->items[0] instanceof ArrayItem && !$listNode->items[1] instanceof ArrayItem) {
            $keyFuncCall = $this->nodeFactory->createFuncCall('key', $eachFuncCall->args);
            $keyFuncCallAssign = new Assign($listNode->items[0]->value, $keyFuncCall);
            return new Expression($keyFuncCallAssign);
        }
        // only value: list(, $value) = each($values);
        if ($listNode->items[1] instanceof ArrayItem && !$listNode->items[0] instanceof ArrayItem) {
            $nextFuncCall = $this->nodeFactory->createFuncCall('next', $eachFuncCall->args);
            $currentFuncCall = $this->nodeFactory->createFuncCall('current', $eachFuncCall->args);
            $secondArrayItem = $listNode->items[1];
            $currentAssign = new Assign($secondArrayItem->value, $currentFuncCall);
            return [new Expression($currentAssign), new Expression($nextFuncCall)];
        }
        // both: list($key, $value) = each($values);
        $currentFuncCall = $this->nodeFactory->createFuncCall('current', $eachFuncCall->args);
        $secondArrayItem = $listNode->items[1];
        if (!$secondArrayItem instanceof ArrayItem) {
            throw new ShouldNotHappenException();
        }
        $currentAssign = new Assign($secondArrayItem->value, $currentFuncCall);
        $nextFuncCall = $this->nodeFactory->createFuncCall('next', $eachFuncCall->args);
        $keyFuncCall = $this->nodeFactory->createFuncCall('key', $eachFuncCall->args);
        $firstArrayItem = $listNode->items[0];
        if (!$firstArrayItem instanceof ArrayItem) {
            throw new ShouldNotHappenException();
        }
        $keyAssign = new Assign($firstArrayItem->value, $keyFuncCall);
        return [new Expression($keyAssign), new Expression($currentAssign), new Expression($nextFuncCall)];
    }
    private function shouldSkipAssign(Assign $assign) : bool
    {
        if (!$this->assignManipulator->isListToEachAssign($assign)) {
            return \true;
        }
        /** @var List_ $listNode */
        $listNode = $assign->var;
        if (\count($listNode->items) !== 2) {
            return \true;
        }
        // empty list → cannot handle
        if ($listNode->items[0] instanceof ArrayItem) {
            return \false;
        }
        return !$listNode->items[1] instanceof ArrayItem;
    }
}
