<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node\CustomNode;

use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Node\FileNode;
/**
 * @deprecated Use @see \Rector\PhpParser\Node\FileNode instead
 * @api
 *
 * Inspired by https://github.com/phpstan/phpstan-src/commit/ed81c3ad0b9877e6122c79b4afda9d10f3994092
 */
final class FileWithoutNamespace extends FileNode implements StmtsAwareInterface
{
    public function getType(): string
    {
        return 'FileWithoutNamespace';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames(): array
    {
        return ['stmts'];
    }
}
