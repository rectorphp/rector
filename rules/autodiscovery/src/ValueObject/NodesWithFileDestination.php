<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\ValueObject;

use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
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
     * @var string|null
     */
    private $oldClassName;

    /**
     * @var string|null
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
        array $nodes, string $fileDestination, SmartFileInfo $originalSmartFileInfo, ?string $oldClassName = null, ?string $newClassName = null
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

    public function getOriginalSmartFileInfo(): SmartFileInfo
    {
        return $this->originalSmartFileInfo;
    }
}
