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
        return \array_filter($namespace->stmts, static function (Stmt $stmt) : bool {
            return $stmt instanceof Use_ || $stmt instanceof GroupUse;
        });
    }
    /**
     * @api
     * @return Use_[]
     */
    public function resolveBareUsesForNode(Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [Namespace_::class, FileWithoutNamespace::class]);
        if (!$namespace instanceof Node) {
            return [];
        }
        return \array_filter($namespace->stmts, static function (Stmt $stmt) : bool {
            return $stmt instanceof Use_;
        });
    }
    /**
     * @param \PhpParser\Node\Stmt\Use_|\PhpParser\Node\Stmt\GroupUse $use
     */
    public function resolvePrefix($use) : string
    {
        return $use instanceof GroupUse ? $use->prefix . '\\' : '';
    }
}
