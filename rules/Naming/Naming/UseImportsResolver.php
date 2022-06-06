<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Naming;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\GroupUse;
use RectorPrefix20220606\PhpParser\Node\Stmt\Namespace_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Use_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class UseImportsResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return Use_[]|GroupUse[]
     */
    public function resolveForNode(Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [Namespace_::class, FileWithoutNamespace::class]);
        if (!$namespace instanceof Node) {
            return [];
        }
        return \array_filter($namespace->stmts, function (Stmt $stmt) : bool {
            return $stmt instanceof Use_ || $stmt instanceof GroupUse;
        });
    }
    /**
     * @return Use_[]
     */
    public function resolveBareUsesForNode(Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [Namespace_::class, FileWithoutNamespace::class]);
        if (!$namespace instanceof Node) {
            return [];
        }
        return \array_filter($namespace->stmts, function (Stmt $stmt) : bool {
            return $stmt instanceof Use_;
        });
    }
}
