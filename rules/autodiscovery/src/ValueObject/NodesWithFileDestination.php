<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\ValueObject;

use PhpParser\Node;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NodesWithFileDestination
{
    /**
     * @var string
     */
    private $fileDestination;

    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var string
     */
    private $oldClassName;

    /**
     * @var string
     */
    private $newClassName;

    /**
     * @var SmartFileInfo
     */
    private $originalSmartFileInfo;

    /**
     * @param Node[] $nodes
     */
    public function __construct(
        array $nodes,
        string $fileDestination,
        string $oldClassName,
        string $newClassName,
        SmartFileInfo $originalSmartFileInfo
    ) {
        $this->nodes = $nodes;
        $this->fileDestination = $fileDestination;
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

    public function getFileDestination(): string
    {
        return $this->fileDestination;
    }

    public function getOldClassName(): string
    {
        return $this->oldClassName;
    }

    public function getNewClassName(): string
    {
        return $this->newClassName;
    }

    public function getOriginalSmartFileInfo(): SmartFileInfo
    {
        return $this->originalSmartFileInfo;
    }
}
