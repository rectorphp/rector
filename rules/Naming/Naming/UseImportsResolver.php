<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
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
     * @return Use_[]
     */
    public function resolveForNode(\PhpParser\Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class]);
        if (!$namespace instanceof \PhpParser\Node) {
            return [];
        }
        $collectedUses = [];
        foreach ($namespace->stmts as $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Use_) {
                $collectedUses[] = $stmt;
                continue;
            }
            if ($stmt instanceof \PhpParser\Node\Stmt\GroupUse) {
                $groupUseUses = [];
                foreach ($stmt->uses as $useUse) {
                    $groupUseUses[] = new \PhpParser\Node\Stmt\UseUse(new \PhpParser\Node\Name($stmt->prefix . '\\' . $useUse->name), $useUse->alias, $useUse->type, $useUse->getAttributes());
                }
                $collectedUses[] = new \PhpParser\Node\Stmt\Use_($groupUseUses, $stmt->type, $stmt->getAttributes());
            }
        }
        return $collectedUses;
    }
}
