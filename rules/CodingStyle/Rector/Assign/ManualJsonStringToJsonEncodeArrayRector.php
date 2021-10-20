<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Assign;

use RectorPrefix20211020\Nette\Utils\Json;
use RectorPrefix20211020\Nette\Utils\JsonException;
use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat as ConcatAssign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CodingStyle\Node\ConcatJoiner;
use Rector\CodingStyle\Node\ConcatManipulator;
use Rector\CodingStyle\NodeFactory\JsonArrayFactory;
use Rector\CodingStyle\NodeFactory\JsonEncodeStaticCallFactory;
use Rector\CodingStyle\ValueObject\ConcatExpressionJoinData;
use Rector\CodingStyle\ValueObject\NodeToRemoveAndConcatItem;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\ManualJsonStringToJsonEncodeArrayRectorTest
 */
final class ManualJsonStringToJsonEncodeArrayRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/85PZHm/1
     */
    private const UNQUOTED_OBJECT_HASH_REGEX = '#(?<start>[^\\"])(?<hash>____\\w+____)#';
    /**
     * @var string
     * @see https://regex101.com/r/jdJ6n9/1
     */
    private const JSON_STRING_REGEX = '#{(.*?\\:.*?)}#s';
    /**
     * @var string
     */
    private const JSON_DATA = 'jsonData';
    /**
     * @var \Rector\CodingStyle\Node\ConcatJoiner
     */
    private $concatJoiner;
    /**
     * @var \Rector\CodingStyle\Node\ConcatManipulator
     */
    private $concatManipulator;
    /**
     * @var \Rector\CodingStyle\NodeFactory\JsonEncodeStaticCallFactory
     */
    private $jsonEncodeStaticCallFactory;
    /**
     * @var \Rector\CodingStyle\NodeFactory\JsonArrayFactory
     */
    private $jsonArrayFactory;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\CodingStyle\Node\ConcatJoiner $concatJoiner, \Rector\CodingStyle\Node\ConcatManipulator $concatManipulator, \Rector\CodingStyle\NodeFactory\JsonEncodeStaticCallFactory $jsonEncodeStaticCallFactory, \Rector\CodingStyle\NodeFactory\JsonArrayFactory $jsonArrayFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->concatJoiner = $concatJoiner;
        $this->concatManipulator = $concatManipulator;
        $this->jsonEncodeStaticCallFactory = $jsonEncodeStaticCallFactory;
        $this->jsonArrayFactory = $jsonArrayFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert manual JSON string to JSON::encode array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Utils\Json;

final class SomeClass
{
    public function run()
    {
        $data = [
            'role_name' => 'admin',
            'numberz' => ['id' => 10]
        ];
        $someJsonAsString = Json::encode($data);
    }
}
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
        if ($node->expr instanceof \PhpParser\Node\Scalar\String_) {
            $stringValue = $node->expr->value;
            // A. full json string
            $isJsonString = $this->isJsonString($stringValue);
            if ($isJsonString) {
                $jsonArray = $this->jsonArrayFactory->createFromJsonString($stringValue);
                $jsonEncodeAssign = $this->createJsonEncodeAssign($node->var, $jsonArray);
                $jsonDataVariable = new \PhpParser\Node\Expr\Variable(self::JSON_DATA);
                $jsonDataAssign = new \PhpParser\Node\Expr\Assign($jsonDataVariable, $jsonArray);
                $this->nodesToAddCollector->addNodeBeforeNode($jsonDataAssign, $node);
                return $jsonEncodeAssign;
            }
            // B. just start of a json? join with all the strings that concat so same variable
            $concatExpressionJoinData = $this->collectContentAndPlaceholderNodesFromNextExpressions($node);
            $stringValue .= $concatExpressionJoinData->getString();
            return $this->removeNodesAndCreateJsonEncodeFromStringValue($concatExpressionJoinData->getNodesToRemove(), $stringValue, $concatExpressionJoinData->getPlaceholdersToNodes(), $node);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            // process only first concat
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentNode instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
                return null;
            }
            $concatStringAndPlaceholders = $this->concatJoiner->joinToStringAndPlaceholderNodes($node->expr);
            // B. just start of a json? join with all the strings that concat so same variable
            $concatExpressionJoinData = $this->collectContentAndPlaceholderNodesFromNextExpressions($node);
            $placeholderNodes = \array_merge($concatStringAndPlaceholders->getPlaceholderNodes(), $concatExpressionJoinData->getPlaceholdersToNodes());
            $stringValue = $concatStringAndPlaceholders->getContent();
            $stringValue .= $concatExpressionJoinData->getString();
            return $this->removeNodesAndCreateJsonEncodeFromStringValue($concatExpressionJoinData->getNodesToRemove(), $stringValue, $placeholderNodes, $node);
        }
        return null;
    }
    private function isJsonString(string $stringValue) : bool
    {
        if (!(bool) \RectorPrefix20211020\Nette\Utils\Strings::match($stringValue, self::JSON_STRING_REGEX)) {
            return \false;
        }
        try {
            return (bool) \RectorPrefix20211020\Nette\Utils\Json::decode($stringValue, \RectorPrefix20211020\Nette\Utils\Json::FORCE_ARRAY);
        } catch (\RectorPrefix20211020\Nette\Utils\JsonException $exception) {
            return \false;
        }
    }
    private function collectContentAndPlaceholderNodesFromNextExpressions(\PhpParser\Node\Expr\Assign $assign) : \Rector\CodingStyle\ValueObject\ConcatExpressionJoinData
    {
        $concatExpressionJoinData = new \Rector\CodingStyle\ValueObject\ConcatExpressionJoinData();
        $currentNode = $assign;
        while ($nextExprAndConcatItem = $this->matchNextExprAssignConcatToSameVariable($assign->var, $currentNode)) {
            $concatItemNode = $nextExprAndConcatItem->getConcatItemNode();
            if ($concatItemNode instanceof \PhpParser\Node\Scalar\String_) {
                $concatExpressionJoinData->addString($concatItemNode->value);
            } elseif ($concatItemNode instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
                $joinToStringAndPlaceholderNodes = $this->concatJoiner->joinToStringAndPlaceholderNodes($concatItemNode);
                $content = $joinToStringAndPlaceholderNodes->getContent();
                $concatExpressionJoinData->addString($content);
                foreach ($joinToStringAndPlaceholderNodes->getPlaceholderNodes() as $placeholder => $expr) {
                    /** @var string $placeholder */
                    $concatExpressionJoinData->addPlaceholderToNode($placeholder, $expr);
                }
            } elseif ($concatItemNode instanceof \PhpParser\Node\Expr) {
                $objectHash = '____' . \spl_object_hash($concatItemNode) . '____';
                $concatExpressionJoinData->addString($objectHash);
                $concatExpressionJoinData->addPlaceholderToNode($objectHash, $concatItemNode);
            }
            $concatExpressionJoinData->addNodeToRemove($nextExprAndConcatItem->getRemovedExpr());
            // jump to next one
            $currentNode = $this->getNextExpression($currentNode);
            if (!$currentNode instanceof \PhpParser\Node) {
                return $concatExpressionJoinData;
            }
        }
        return $concatExpressionJoinData;
    }
    /**
     * @param Node[] $nodesToRemove
     * @param Expr[] $placeholderNodes
     */
    private function removeNodesAndCreateJsonEncodeFromStringValue(array $nodesToRemove, string $stringValue, array $placeholderNodes, \PhpParser\Node\Expr\Assign $assign) : ?\PhpParser\Node\Expr\Assign
    {
        $stringValue = \RectorPrefix20211020\Nette\Utils\Strings::replace($stringValue, self::UNQUOTED_OBJECT_HASH_REGEX, '$1"$2"');
        if (!$this->isJsonString($stringValue)) {
            return null;
        }
        $this->removeNodes($nodesToRemove);
        $jsonArray = $this->jsonArrayFactory->createFromJsonStringAndPlaceholders($stringValue, $placeholderNodes);
        $jsonDataVariable = new \PhpParser\Node\Expr\Variable(self::JSON_DATA);
        $jsonDataAssign = new \PhpParser\Node\Expr\Assign($jsonDataVariable, $jsonArray);
        $this->nodesToAddCollector->addNodeBeforeNode($jsonDataAssign, $assign);
        return $this->createJsonEncodeAssign($assign->var, $jsonArray);
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|ConcatAssign|\PhpParser\Node\Stmt\Expression|\PhpParser\Node $currentNode
     */
    private function matchNextExprAssignConcatToSameVariable(\PhpParser\Node\Expr $expr, $currentNode) : ?\Rector\CodingStyle\ValueObject\NodeToRemoveAndConcatItem
    {
        $nextExpression = $this->getNextExpression($currentNode);
        if (!$nextExpression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $nextExpressionNode = $nextExpression->expr;
        if ($nextExpressionNode instanceof \PhpParser\Node\Expr\AssignOp\Concat) {
            // is assign to same variable?
            if (!$this->nodeComparator->areNodesEqual($expr, $nextExpressionNode->var)) {
                return null;
            }
            return new \Rector\CodingStyle\ValueObject\NodeToRemoveAndConcatItem($nextExpressionNode, $nextExpressionNode->expr);
        }
        // $value = $value . '...';
        if ($nextExpressionNode instanceof \PhpParser\Node\Expr\Assign) {
            if (!$nextExpressionNode->expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
                return null;
            }
            // is assign to same variable?
            if (!$this->nodeComparator->areNodesEqual($expr, $nextExpressionNode->var)) {
                return null;
            }
            $firstConcatItem = $this->concatManipulator->getFirstConcatItem($nextExpressionNode->expr);
            // is the first concat the same variable
            if (!$this->nodeComparator->areNodesEqual($expr, $firstConcatItem)) {
                return null;
            }
            // return all but first node
            $allButFirstConcatItem = $this->concatManipulator->removeFirstItemFromConcat($nextExpressionNode->expr);
            return new \Rector\CodingStyle\ValueObject\NodeToRemoveAndConcatItem($nextExpressionNode, $allButFirstConcatItem);
        }
        return null;
    }
    private function getNextExpression(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $currentExpression = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentExpression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        return $currentExpression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
    }
    private function createJsonEncodeAssign(\PhpParser\Node\Expr $assignExpr, \PhpParser\Node\Expr\Array_ $jsonArray) : \PhpParser\Node\Expr\Assign
    {
        if ($this->reflectionProvider->hasClass('Nette\\Utils\\Json')) {
            return $this->jsonEncodeStaticCallFactory->createFromArray($assignExpr, $jsonArray);
        }
        $jsonDataAssign = new \PhpParser\Node\Expr\Assign($assignExpr, $jsonArray);
        $jsonDataVariable = new \PhpParser\Node\Expr\Variable(self::JSON_DATA);
        $jsonDataAssign->expr = $this->nodeFactory->createFuncCall('json_encode', [$jsonDataVariable]);
        return $jsonDataAssign;
    }
}
