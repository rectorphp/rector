<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\PhpParser\Node\BetterNodeFinder;

final class FunctionLikeMaintainer
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeAnalyzer $nodeTypeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    /**
     * Based on static analysis of code, looking for return types
     * @param ClassMethod|Function_ $node
     */
    public function resolveStaticReturnTypeInfo(Node $node): ?ReturnTypeInfo
    {
        /** @var Return_[] $returnNodes */
        $returnNodes = $this->betterNodeFinder->findInstanceOf($node->stmts, Return_::class);

        $types = [];
        foreach ($returnNodes as $returnNode) {
            $types = array_merge($types, $this->nodeTypeAnalyzer->resolveSingleTypeToStrings($returnNode->expr));
        }

        $types = array_filter($types);
        return new ReturnTypeInfo($types);
    }
}
