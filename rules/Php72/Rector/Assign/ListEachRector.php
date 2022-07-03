<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(AssignManipulator $assignManipulator, NodesToAddCollector $nodesToAddCollector)
    {
        $this->assignManipulator = $assignManipulator;
        $this->nodesToAddCollector = $nodesToAddCollector;
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
        /** @var List_ $listNode */
        $listNode = $node->var;
        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $node->expr;
        // only key: list($key, ) = each($values);
        if ($listNode->items[0] instanceof ArrayItem && $listNode->items[1] === null) {
            $keyFuncCall = $this->nodeFactory->createFuncCall('key', $eachFuncCall->args);
            return new Assign($listNode->items[0]->value, $keyFuncCall);
        }
        // only value: list(, $value) = each($values);
        if ($listNode->items[1] instanceof ArrayItem && $listNode->items[0] === null) {
            $nextFuncCall = $this->nodeFactory->createFuncCall('next', $eachFuncCall->args);
            $this->nodesToAddCollector->addNodeAfterNode($nextFuncCall, $node);
            $currentFuncCall = $this->nodeFactory->createFuncCall('current', $eachFuncCall->args);
            $secondArrayItem = $listNode->items[1];
            return new Assign($secondArrayItem->value, $currentFuncCall);
        }
        // both: list($key, $value) = each($values);
        $currentFuncCall = $this->nodeFactory->createFuncCall('current', $eachFuncCall->args);
        $secondArrayItem = $listNode->items[1];
        if (!$secondArrayItem instanceof ArrayItem) {
            throw new ShouldNotHappenException();
        }
        $assign = new Assign($secondArrayItem->value, $currentFuncCall);
        $this->nodesToAddCollector->addNodeAfterNode($assign, $node);
        $nextFuncCall = $this->nodeFactory->createFuncCall('next', $eachFuncCall->args);
        $this->nodesToAddCollector->addNodeAfterNode($nextFuncCall, $node);
        $keyFuncCall = $this->nodeFactory->createFuncCall('key', $eachFuncCall->args);
        $firstArrayItem = $listNode->items[0];
        if (!$firstArrayItem instanceof ArrayItem) {
            throw new ShouldNotHappenException();
        }
        return new Assign($firstArrayItem->value, $keyFuncCall);
    }
    private function shouldSkip(Assign $assign) : bool
    {
        if (!$this->assignManipulator->isListToEachAssign($assign)) {
            return \true;
        }
        // assign should be top level, e.g. not in a while loop
        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Expression) {
            return \true;
        }
        /** @var List_ $listNode */
        $listNode = $assign->var;
        if (\count($listNode->items) !== 2) {
            return \true;
        }
        // empty list → cannot handle
        if ($listNode->items[0] !== null) {
            return \false;
        }
        return $listNode->items[1] === null;
    }
}
