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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\CodingStyle\Node\ConcatJoiner;
use Rector\CodingStyle\Node\ConcatManipulator;
use Rector\CodingStyle\ValueObject\ConcatExpressionJoinData;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
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

            // B. just start of a json?
            $currentNode = $node;

            $concatExpressionJoinData = $this->collectContentAndPlaceholderNodesFromNextExpressions(
                $node,
                $currentNode
            );

            $stringValue .= $concatExpressionJoinData->getString();
            if (! $this->isJsonString($stringValue)) {
                return null;
            }

            // remove nodes
            $this->removeNodes($concatExpressionJoinData->getNodesToRemove());

            $jsonArray = $this->createArrayNodeFromJsonString($stringValue);

            $this->replaceNodeObjectHashPlaceholdersWithNodes(
                $jsonArray,
                $concatExpressionJoinData->getPlaceholdersToNodes()
            );

            return $this->createAndReturnJsonEncodeFromArray($node, $jsonArray);
        }

        if ($node->expr instanceof Concat) {
            // process only first concat
            $concatParentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($concatParentNode instanceof Concat) {
                return null;
            }

            /** @var Expr[] $placeholderNodes */
            [$content, $placeholderNodes] = $this->concatJoiner->joinToStringAndPlaceholderNodes($node->expr);

            /** @var string $content */
            if (! $this->isJsonString($content)) {
                return null;
            }

            $jsonArray = $this->createArrayNodeFromJsonString($content);
            $this->replaceNodeObjectHashPlaceholdersWithNodes($jsonArray, $placeholderNodes);

            return $this->createAndReturnJsonEncodeFromArray($node, $jsonArray);
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
        // traverse and replace placeholdes by original nodes
        $this->traverseNodesWithCallable([$array], function (Node $node) use ($placeholderNodes): ?Expr {
            if (! $node instanceof String_) {
                return null;
            }

            $stringValue = $node->value;

            return $placeholderNodes[$stringValue] ?? null;
        });
    }

    /**
     * @param Assign|ConcatAssign $currentNode
     * @return Node[]|null
     */
    private function matchNextExpressionAssignConcatToSameVariable(Expr $expr, Node $currentNode): ?array
    {
        $nextExpression = $this->getNextExpression($currentNode);
        if (! $nextExpression instanceof Node\Stmt\Expression) {
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

    private function collectContentAndPlaceholderNodesFromNextExpressions(
        Assign $assign,
        Node $currentNode
    ): ConcatExpressionJoinData {
        $concatExpressionJoinData = new ConcatExpressionJoinData();

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
                $objectHash = spl_object_hash($valueNode);
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
}
