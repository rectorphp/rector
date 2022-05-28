<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class UseImportsResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return Use_[]|GroupUse[]
     */
    public function resolveForNode(\PhpParser\Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class]);
        if (!$namespace instanceof \PhpParser\Node) {
            return [];
        }
        return \array_filter($namespace->stmts, function (\PhpParser\Node\Stmt $stmt) : bool {
            return $stmt instanceof \PhpParser\Node\Stmt\Use_ || $stmt instanceof \PhpParser\Node\Stmt\GroupUse;
        });
    }
    /**
     * @return Use_[]
     */
    public function resolveBareUsesForNode(\PhpParser\Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class]);
        if (!$namespace instanceof \PhpParser\Node) {
            return [];
        }
        return \array_filter($namespace->stmts, function (\PhpParser\Node\Stmt $stmt) : bool {
            return $stmt instanceof \PhpParser\Node\Stmt\Use_;
        });
    }
}
