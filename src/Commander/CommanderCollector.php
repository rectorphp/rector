<?php declare(strict_types=1);

namespace Rector\Commander;

use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\Exception\ShouldNotHappenException;

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
