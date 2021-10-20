<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeFactory;

use RectorPrefix20211020\Nette\Utils\Json;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\CodingStyle\NodeAnalyzer\ImplodeAnalyzer;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class JsonArrayFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\CodingStyle\NodeAnalyzer\ImplodeAnalyzer
     */
    private $implodeAnalyzer;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\CodingStyle\NodeAnalyzer\ImplodeAnalyzer $implodeAnalyzer, \RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeFactory = $nodeFactory;
        $this->implodeAnalyzer = $implodeAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function createFromJsonString(string $stringValue) : \PhpParser\Node\Expr\Array_
    {
        $array = \RectorPrefix20211020\Nette\Utils\Json::decode($stringValue, \RectorPrefix20211020\Nette\Utils\Json::FORCE_ARRAY);
        return $this->nodeFactory->createArray($array);
    }
    /**
     * @param Expr[] $placeholderNodes
     */
    public function createFromJsonStringAndPlaceholders(string $jsonString, array $placeholderNodes) : \PhpParser\Node\Expr\Array_
    {
        $jsonArray = $this->createFromJsonString($jsonString);
        $this->replaceNodeObjectHashPlaceholdersWithNodes($jsonArray, $placeholderNodes);
        return $jsonArray;
    }
    /**
     * @param Expr[] $placeholderNodes
     */
    private function replaceNodeObjectHashPlaceholdersWithNodes(\PhpParser\Node\Expr\Array_ $array, array $placeholderNodes) : void
    {
        // traverse and replace placeholder by original nodes
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($array, function (\PhpParser\Node $node) use($placeholderNodes) : ?Expr {
            if ($node instanceof \PhpParser\Node\Expr\Array_ && \count($node->items) === 1) {
                $onlyItem = $node->items[0];
                if (!$onlyItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    throw new \Rector\Core\Exception\ShouldNotHappenException();
                }
                $placeholderNode = $this->matchPlaceholderNode($onlyItem->value, $placeholderNodes);
                if ($placeholderNode && $this->implodeAnalyzer->isImplodeToJson($placeholderNode)) {
                    /**
                     * @var FuncCall $placeholderNode
                     * @var Arg $firstArg
                     *
                     * Arg check already on $this->implodeAnalyzer->isImplodeToJson() above
                     */
                    $firstArg = $placeholderNode->args[1];
                    return $firstArg->value;
                }
            }
            return $this->matchPlaceholderNode($node, $placeholderNodes);
        });
    }
    /**
     * @param Expr[] $placeholderNodes
     */
    private function matchPlaceholderNode(\PhpParser\Node $node, array $placeholderNodes) : ?\PhpParser\Node\Expr
    {
        if (!$node instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        return $placeholderNodes[$node->value] ?? null;
    }
}
