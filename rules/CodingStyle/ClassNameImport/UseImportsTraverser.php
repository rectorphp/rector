<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class UseImportsTraverser
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Stmt[] $stmts
     * @param callable(Use_::TYPE_* $useType, UseItem $useUse, string $name):void $callable
     */
    public function traverserStmts(array $stmts, callable $callable) : void
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_ || $stmt instanceof FileWithoutNamespace) {
                $this->traverserStmts($stmt->stmts, $callable);
                continue;
            }
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $useUse) {
                    $name = $this->nodeNameResolver->getName($useUse);
                    if ($name === null) {
                        continue;
                    }
                    $callable($stmt->type, $useUse, $name);
                }
                continue;
            }
            if ($stmt instanceof GroupUse) {
                $this->processGroupUse($stmt, $callable);
            }
        }
    }
    /**
     * @param callable(Use_::TYPE_* $useType, UseItem $useUse, string $name):void $callable
     */
    private function processGroupUse(GroupUse $groupUse, callable $callable) : void
    {
        if ($groupUse->type !== Use_::TYPE_UNKNOWN) {
            return;
        }
        $prefixName = $groupUse->prefix->toString();
        foreach ($groupUse->uses as $useUse) {
            $name = $prefixName . '\\' . $this->nodeNameResolver->getName($useUse);
            $callable($useUse->type, $useUse, $name);
        }
    }
}
