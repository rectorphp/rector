<?php

declare (strict_types=1);
namespace Rector\Core\Validation;

use PhpParser\Node;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class InfiniteLoopValidator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param mixed[]|\PhpParser\Node $node
     */
    public function isValid($node, \PhpParser\Node $originalNode, string $rectorClass) : bool
    {
        if ($this->nodeComparator->areNodesEqual($node, $originalNode)) {
            return \true;
        }
        $isFound = (bool) $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $subNode) use($node) : bool {
            return $this->nodeComparator->areNodesEqual($node, $subNode);
        });
        if (!$isFound) {
            return \true;
        }
        $createdByRule = $originalNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [];
        if ($createdByRule === []) {
            return \true;
        }
        return !\in_array($rectorClass, $createdByRule, \true);
    }
}
