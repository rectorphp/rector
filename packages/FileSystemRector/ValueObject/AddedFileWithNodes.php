<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\ValueObject;

use PhpParser\Node;
use Rector\FileSystemRector\Contract\AddedFileInterface;
use Rector\FileSystemRector\Contract\FileWithNodesInterface;

final class AddedFileWithNodes implements AddedFileInterface, FileWithNodesInterface
{
    /**
     * @param Node[] $nodes
     */
    public function __construct(
        private string $filePath,
        private array $nodes
    ) {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
