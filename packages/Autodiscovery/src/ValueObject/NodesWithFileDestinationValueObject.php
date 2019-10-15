<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\ValueObject;

use PhpParser\Node;

final class NodesWithFileDestinationValueObject
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var string
     */
    private $fileDestination;

    /**
     * @param Node[] $nodes
     */
    public function __construct(array $nodes, string $fileDestination)
    {
        $this->nodes = $nodes;
        $this->fileDestination = $fileDestination;
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
}
