<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\CodingStyle\Node\ConcatJoiner;
use Rector\Exception\NotImplementedException;
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

    public function __construct(ConcatJoiner $concatJoiner)
    {
        $this->concatJoiner = $concatJoiner;
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

            $currentNode = $node;

            /** @var Expr[] $placeholderNodes */
            $placeholderNodes = [];
            $content = '';

            while ($nextExpressionNode = $this->matchNextExpressionAssignConcatToSameVariable(
                $node->var,
                $currentNode
            )) {
                if ($nextExpressionNode->expr instanceof String_) {
                    $content .= $nextExpressionNode->expr->value;
                } elseif ($nextExpressionNode->expr instanceof Concat) {
                    [$newContent, $placeholderNodes] = $this->concatJoiner->joinToStringAndPlaceholderNodes(
                        $nextExpressionNode->expr
                    );
                    $content .= $newContent;
                } elseif ($nextExpressionNode->expr instanceof Expr) {
                    $objectHash = spl_object_hash($nextExpressionNode->expr);
                    $placeholderNodes[$objectHash] = $nextExpressionNode->expr;
                    $content .= $objectHash;
                } else {
                    throw new NotImplementedException('Fix it! ' . __METHOD__);
                }

                // jump to next one
                $currentNode = $this->getNextExpression($currentNode);

                $this->removeNode($nextExpressionNode);
            }

            /** @var string $content */
            $stringValue .= $content;
            if (! $this->isJsonString($stringValue)) {
                return null;
            }

            $array = Json::decode($stringValue, Json::FORCE_ARRAY);

            $jsonArray = $this->createArray($array);

            /** @var Expr[] $placeholderNodes */
            $this->replaceNodeObjectHashPlaceholdersWithNodes($jsonArray, $placeholderNodes);

            return $this->createAndReturnJsonEncodeFromArray($node, $jsonArray);
        }

        if ($node->expr instanceof Concat) {
            // process only first concat
            $concatParentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($concatParentNode instanceof Concat) {
                return null;
            }

            /** @var string $content */
            /** @var Expr[] $placeholderNodes */
            [$content, $placeholderNodes] = $this->concatJoiner->joinToStringAndPlaceholderNodes($node->expr);

            if (! $this->isJsonString($content)) {
                return null;
            }

            $array = Json::decode($content, Json::FORCE_ARRAY);

            $jsonArray = $this->createArray($array);

            $this->replaceNodeObjectHashPlaceholdersWithNodes($jsonArray, $placeholderNodes);

            return $this->createAndReturnJsonEncodeFromArray($node, $jsonArray);
        }

        return null;
    }

    private function processJsonString(Assign $assign, string $stringValue): Node
    {
        $array = Json::decode($stringValue, Json::FORCE_ARRAY);
        $arrayNode = $this->createArray($array);

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
     * @param Assign|Expr\AssignOp\Concat $currentNode
     */
    private function matchNextExpressionAssignConcatToSameVariable(Expr $expr, Node $currentNode): ?Expr\AssignOp\Concat
    {
        $nextExpression = $this->getNextExpression($currentNode);
        if (! $nextExpression instanceof Node\Stmt\Expression) {
            return null;
        }

        $nextExpressionNode = $nextExpression->expr;
        if (! $nextExpressionNode instanceof Expr\AssignOp\Concat) {
            return null;
        }

        // is assign to same variable?
        if (! $this->areNodesEqual($expr, $nextExpressionNode->var)) {
            return null;
        }

        return $nextExpressionNode;
    }
}
