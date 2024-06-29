<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\ValueObject\Application\File;
final class UseImportsResolver
{
    /**
     * @readonly
     * @var \Rector\Application\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @return array<Use_|GroupUse>
     */
    public function resolve() : array
    {
        $namespace = $this->resolveNamespace();
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
    public function resolveBareUses() : array
    {
        $namespace = $this->resolveNamespace();
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
    /**
     * @return \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|null
     */
    private function resolveNamespace()
    {
        /** @var File|null $file */
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        $newStmts = $file->getNewStmts();
        if ($newStmts === []) {
            return null;
        }
        $namespaces = \array_filter($newStmts, static function (Stmt $stmt) : bool {
            return $stmt instanceof Namespace_;
        });
        // multiple namespaces is not supported
        if (\count($namespaces) > 1) {
            return null;
        }
        $currentNamespace = \current($namespaces);
        if ($currentNamespace instanceof Namespace_) {
            return $currentNamespace;
        }
        $currentStmt = \current($newStmts);
        if (!$currentStmt instanceof FileWithoutNamespace) {
            return null;
        }
        return $currentStmt;
    }
}
