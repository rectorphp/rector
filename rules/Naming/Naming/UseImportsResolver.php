<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\PhpParser\Node\FileNode;
use Rector\ValueObject\Application\File;
final class UseImportsResolver
{
    /**
     * @readonly
     */
    private CurrentFileProvider $currentFileProvider;
    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @return array<Use_|GroupUse>
     */
    public function resolve(): array
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return [];
        }
        $rootNode = $file->getFileNode();
        if (!$rootNode instanceof FileNode) {
            return [];
        }
        return $rootNode->getUsesAndGroupUses();
    }
    /**
     * @api
     * @return Use_[]
     */
    public function resolveBareUses(): array
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return [];
        }
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return [];
        }
        return $fileNode->getUses();
    }
    /**
     * @param \PhpParser\Node\Stmt\Use_|\PhpParser\Node\Stmt\GroupUse $use
     */
    public function resolvePrefix($use): string
    {
        return $use instanceof GroupUse ? $use->prefix . '\\' : '';
    }
}
