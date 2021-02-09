<?php
declare(strict_types=1);

namespace Rector\FileSystemRector\ValueObject;

use PhpParser\Node;
use Rector\FileSystemRector\Contract\AddedFileInterface;

final class AddedFileWithNodes implements AddedFileInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Node[]
     */
    private $nodes;

    /**
     * @param Node[] $nodes
     */
    public function __construct(string $filePath, array $nodes)
    {
        $this->filePath = $filePath;
        $this->nodes = $nodes;
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
