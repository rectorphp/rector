<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat as ConcatAssign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\CodingStyle\Node\ConcatJoiner;
use Rector\CodingStyle\Node\ConcatManipulator;
use Rector\CodingStyle\ValueObject\ConcatExpressionJoinData;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\ManualJsonStringToJsonEncodeArrayRectorTest
 */
final class ManualJsonStringToJsonEncodeArrayRector extends AbstractRector
{
    /**
     * @var ConcatJoiner
     */
    private $concatJoiner;

    /**
     * @var ConcatManipulator
     */
    private $concatManipulator;

    public function __construct(ConcatJoiner $concatJoiner, ConcatManipulator $concatManipulator)
    {
        $this->concatJoiner = $concatJoiner;
        $this->concatManipulator = $concatManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add extra space before new assign set', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $data = [
            'role_name' => 'admin',
            'numberz' => ['id' => 10]
        ];

        $someJsonAsString = json_encode($data);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->expr instanceof String_) {
            $stringValue = $node->expr->value;

            // A. full json string
            $isJsonString = $this->isJsonString($stringValue);
            if ($isJsonString) {
                return $this->processJsonString($node, $stringValue);
            }

            // B. just start of a json? join with all the strings that concat so same variable
            $concatExpressionJoinData = $this->collectContentAndPlaceholderNodesFromNextExpressions($node);

            $stringValue .= $concatExpressionJoinData->getString();

            return $this->removeNodesAndCreateJsonEncodeFromStringValue(
                $concatExpressionJoinData->getNodesToRemove(),
                $stringValue,
                $concatExpressionJoinData->getPlaceholdersToNodes(),
                $node
            );
        }

        if ($node->expr instanceof Concat) {
            // process only first concat
            if ($node->getAttribute(AttributeKey::PARENT_NODE) instanceof Concat) {
                return null;
            }

            /** @var Expr[] $placeholderNodes */
            [$stringValue, $placeholderNodes] = $this->concatJoiner->joinToStringAndPlaceholderNodes($node->expr);

            // B. just start of a json? join with all the strings that concat so same variable
            $concatExpressionJoinData = $this->collectContentAndPlaceholderNodesFromNextExpressions($node);

            $placeholderNodes = array_merge($placeholderNodes, $concatExpressionJoinData->getPlaceholdersToNodes());

            /** @var string $stringValue */
            $stringValue .= $concatExpressionJoinData->getString();

            return $this->removeNodesAndCreateJsonEncodeFromStringValue(
                $concatExpressionJoinData->getNodesToRemove(),
                $stringValue,
                $placeholderNodes,
                $node
            );
        }

        return null;
    }

    private function processJsonString(Assign $assign, string $stringValue): Node
    {
        $arrayNode = $this->createArrayNodeFromJsonString($stringValue);

        return $this->createAndReturnJsonEncodeFromArray($assign, $arrayNode);
    }

    private function isJsonString(string $stringValue): bool
    {
        if (! (bool) Strings::match($stringValue, '#{(.*?\:.*?)}#')) {
            return false;
        }

        try {
            return (bool) Json::decode($stringValue, Json::FORCE_ARRAY);
        } catch (JsonException $jsonException) {
            return false;
        }
    }

    /**
     * Creates + adds
     *
     * $jsonData = ['...'];
     * $json = Nette\Utils\Json::encode($jsonData);
     */
    private function createAndReturnJsonEncodeFromArray(Assign $assign, Array_ $jsonArray): Assign
    {
        $jsonDataVariable = new Variable('jsonData');

        $jsonDataAssign = new Assign($jsonDataVariable, $jsonArray);
        $this->addNodeBeforeNode($jsonDataAssign, $assign);

        $assign->expr = $this->createStaticCall('Nette\Utils\Json', 'encode', [$jsonDataVariable]);

        return $assign;
    }

    /**
     * @param Expr[] $placeholderNodes
     */
    private function replaceNodeObjectHashPlaceholdersWithNodes(Array_ $array, array $placeholderNodes): void
    {
        // traverse and replace placeholder by original nodes
        $this->traverseNodesWithCallable($array, function (Node $node) use ($placeholderNodes): ?Expr {
            if ($node instanceof Array_ && count($node->items) === 1) {
                $placeholderNode = $this->matchPlaceholderNode($node->items[0]->value, $placeholderNodes);

                if ($placeholderNode && $this->isImplodeToJson($placeholderNode)) {
                    /** @var FuncCall $placeholderNode */
                    return $placeholderNode->args[1]->value;
                }
            }

            return $this->matchPlaceholderNode($node, $placeholderNodes);
        });
    }

    /**
     * @param Assign|ConcatAssign $currentNode
     * @return Node[]|null
     */
    private function matchNextExpressionAssignConcatToSameVariable(Expr $expr, Node $currentNode): ?array
    {
        $nextExpression = $this->getNextExpression($currentNode);
        if (! $nextExpression instanceof Expression) {
            return null;
        }

        $nextExpressionNode = $nextExpression->expr;

        // $value .= '...';
        if ($nextExpressionNode instanceof ConcatAssign) {
            // is assign to same variable?
            if (! $this->areNodesEqual($expr, $nextExpressionNode->var)) {
                return null;
            }

            return [$nextExpressionNode, $nextExpressionNode->expr];
        }

        // $value = $value . '...';
        if ($nextExpressionNode instanceof Assign) {
            if (! $nextExpressionNode->expr instanceof Concat) {
                return null;
            }

            // is assign to same variable?
            if (! $this->areNodesEqual($expr, $nextExpressionNode->var)) {
                return null;
            }

            $firstConcatItem = $this->concatManipulator->getFirstConcatItem($nextExpressionNode->expr);

            // is the first concat the same variable
            if (! $this->areNodesEqual($expr, $firstConcatItem)) {
                return null;
            }

            // return all but first node
            $allButFirstConcatItem = $this->concatManipulator->removeFirstItemFromConcat($nextExpressionNode->expr);

            return [$nextExpressionNode, $allButFirstConcatItem];
        }

        return null;
    }

    private function createArrayNodeFromJsonString(string $stringValue): Array_
    {
        $array = Json::decode($stringValue, Json::FORCE_ARRAY);

        return $this->createArray($array);
    }

    private function collectContentAndPlaceholderNodesFromNextExpressions(Assign $assign): ConcatExpressionJoinData
    {
        $concatExpressionJoinData = new ConcatExpressionJoinData();

        $currentNode = $assign;

        while ([$nodeToRemove, $valueNode] = $this->matchNextExpressionAssignConcatToSameVariable(
            $assign->var,
            $currentNode
        )) {
            if ($valueNode instanceof String_) {
                $concatExpressionJoinData->addString($valueNode->value);
            } elseif ($valueNode instanceof Concat) {
                /** @var Expr[] $newPlaceholderNodes */
                [$content, $newPlaceholderNodes] = $this->concatJoiner->joinToStringAndPlaceholderNodes($valueNode);
                /** @var string $content */
                $concatExpressionJoinData->addString($content);

                foreach ($newPlaceholderNodes as $placeholder => $expr) {
                    /** @var string $placeholder */
                    $concatExpressionJoinData->addPlaceholderToNode($placeholder, $expr);
                }
            } elseif ($valueNode instanceof Expr) {
                $objectHash = '____' . spl_object_hash($valueNode) . '____';

                $concatExpressionJoinData->addString($objectHash);
                $concatExpressionJoinData->addPlaceholderToNode($objectHash, $valueNode);
            }

            $concatExpressionJoinData->addNodeToRemove($nodeToRemove);

            // jump to next one
            $currentNode = $this->getNextExpression($currentNode);
            if ($currentNode === null) {
                return $concatExpressionJoinData;
            }
        }

        return $concatExpressionJoinData;
    }

    /**
     * Matches: "implode('","', $items)"
     */
    private function isImplodeToJson(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $this->isName($node, 'implode')) {
            return false;
        }

        if (! isset($node->args[1])) {
            return false;
        }

        $firstArgumentValue = $node->args[0]->value;
        if ($firstArgumentValue instanceof String_) {
            if ($firstArgumentValue->value !== '","') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Expr[] $placeholderNodes
     */
    private function matchPlaceholderNode(Node $node, array $placeholderNodes): ?Expr
    {
        if (! $node instanceof String_) {
            return null;
        }

        return $placeholderNodes[$node->value] ?? null;
    }

    /**
     * @param Node[] $nodesToRemove
     * @param Expr[] $placeholderNodes
     */
    private function removeNodesAndCreateJsonEncodeFromStringValue(
        array $nodesToRemove,
        string $stringValue,
        array $placeholderNodes,
        Assign $assign
    ): ?Assign {
        // quote object hashes if needed - https://regex101.com/r/85PZHm/1
        $stringValue = Strings::replace($stringValue, '#(?<start>[^\"])(?<hash>____\w+____)#', '$1"$2"');
        if (! $this->isJsonString($stringValue)) {
            return null;
        }

        $this->removeNodes($nodesToRemove);

        $jsonArray = $this->createArrayNodeFromJsonString($stringValue);
        $this->replaceNodeObjectHashPlaceholdersWithNodes($jsonArray, $placeholderNodes);

        return $this->createAndReturnJsonEncodeFromArray($assign, $jsonArray);
    }
}
