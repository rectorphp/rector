<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\ValueObject;

use PhpParser\Node;
use Rector\FileSystemRector\Contract\AddedFileInterface;
use Rector\FileSystemRector\Contract\FileWithNodesInterface;
final class AddedFileWithNodes implements \Rector\FileSystemRector\Contract\AddedFileInterface, \Rector\FileSystemRector\Contract\FileWithNodesInterface
{
    /**
     * @var string
     */
    private $filePath;
    /**
     * @var \PhpParser\Node[]
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
    public function getFilePath() : string
    {
        return $this->filePath;
    }
    /**
     * @return Node[]
     */
    public function getNodes() : array
    {
        return $this->nodes;
    }
}
