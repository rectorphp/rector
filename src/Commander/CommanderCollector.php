<?php

declare(strict_types=1);

namespace Rector\Core\Commander;

use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\Exception\ShouldNotHappenException;

final class CommanderCollector
{
    /**
     * @var CommanderInterface[]
     */
    private $commanders = [];

    /**
     * @param CommanderInterface[] $commanders
     */
    public function __construct(array $commanders)
    {
        $this->sortAndSetCommanders($commanders);
    }

    /**
     * @return CommanderInterface[]
     */
    public function provide(): array
    {
        return $this->commanders;
    }

    /**
     * @param CommanderInterface[] $commanders
     */
    private function sortAndSetCommanders(array $commanders): void
    {
        $commandersByPriority = [];

        foreach ($commanders as $commander) {
            if (isset($commandersByPriority[$commander->getPriority()])) {
                throw new ShouldNotHappenException();
            }

            $commandersByPriority[$commander->getPriority()] = $commander;
        }

        krsort($commandersByPriority);

        $this->commanders = $commandersByPriority;
    }
}
