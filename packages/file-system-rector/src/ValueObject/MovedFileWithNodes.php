<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\ValueObject;

use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\FileSystemRector\Contract\FileWithNodesInterface;
use Rector\FileSystemRector\Contract\MovedFileInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MovedFileWithNodes implements MovedFileInterface, FileWithNodesInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var SmartFileInfo
     */
    private $originalSmartFileInfo;

    /**
     * @var string|null
     */
    private $oldClassName;

    /**
     * @var string|null
     */
    private $newClassName;

    /**
     * @param Node[] $nodes
     */
    public function __construct(
        array $nodes,
        string $fileDestination,
        SmartFileInfo $originalSmartFileInfo,
        ?string $oldClassName = null,
        ?string $newClassName = null
    ) {
        $this->nodes = $nodes;
        $this->filePath = $fileDestination;
        $this->oldClassName = $oldClassName;
        $this->newClassName = $newClassName;
        $this->originalSmartFileInfo = $originalSmartFileInfo;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getNewPathname(): string
    {
        return $this->filePath;
    }

    public function getOldClassName(): string
    {
        if ($this->oldClassName === null) {
            throw new ShouldNotHappenException();
        }

        return $this->oldClassName;
    }

    public function getNewClassName(): string
    {
        if ($this->newClassName === null) {
            throw new ShouldNotHappenException();
        }

        return $this->newClassName;
    }

    public function hasClassRename(): bool
    {
        return $this->newClassName !== null;
    }

    public function getOriginalFileInfo(): SmartFileInfo
    {
        return $this->originalSmartFileInfo;
    }

    public function getOldPathname(): string
    {
        return $this->originalSmartFileInfo->getPathname();
    }
}
