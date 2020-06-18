<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\ValueObject;

use PhpParser\Node;

final class NodesWithFileDestinationValueObject
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
     * @param Node[] $nodes
     */
    public function __construct(array $nodes, string $fileDestination, string $oldClassName, string $newClassName)
    {
        $this->nodes = $nodes;
        $this->fileDestination = $fileDestination;
        $this->oldClassName = $oldClassName;
        $this->newClassName = $newClassName;
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
}
