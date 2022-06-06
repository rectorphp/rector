<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\FileSystemRector\ValueObject;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\FileSystemRector\Contract\AddedFileInterface;
use RectorPrefix20220606\Rector\FileSystemRector\Contract\FileWithNodesInterface;
final class AddedFileWithNodes implements AddedFileInterface, FileWithNodesInterface
{
    /**
     * @readonly
     * @var string
     */
    private $filePath;
    /**
     * @var Node\Stmt[]
     * @readonly
     */
    private $nodes;
    /**
     * @param Node\Stmt[] $nodes
     */
    public function __construct(string $filePath, array $nodes)
    {
        $this->filePath = $filePath;
        $this->nodes = $nodes;
    }
    public function getFilePath() : string
    {
        return $this->filePath;
    }
    /**
     * @return Node\Stmt[]
     */
    public function getNodes() : array
    {
        return $this->nodes;
    }
}
