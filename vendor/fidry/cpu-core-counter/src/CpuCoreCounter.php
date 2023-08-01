<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202308\Fidry\CpuCoreCounter;

use RectorPrefix202308\Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use RectorPrefix202308\Fidry\CpuCoreCounter\Finder\FinderRegistry;
final class CpuCoreCounter
{
    /**
     * @var list<CpuCoreFinder>
     */
    private $finders;
    /**
     * @var positive-int|null
     */
    private $count;
    /**
     * @param list<CpuCoreFinder>|null $finders
     */
    public function __construct(?array $finders = null)
    {
        $this->finders = $finders ?? FinderRegistry::getDefaultLogicalFinders();
    }
    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    public function getCount() : int
    {
        // Memoize result
        if (null === $this->count) {
            $this->count = $this->findCount();
        }
        return $this->count;
    }
    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    private function findCount() : int
    {
        foreach ($this->finders as $finder) {
            $cores = $finder->find();
            if (null !== $cores) {
                return $cores;
            }
        }
        throw NumberOfCpuCoreNotFound::create();
    }
    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return array{CpuCoreFinder, positive-int}
     */
    public function getFinderAndCores() : array
    {
        foreach ($this->finders as $finder) {
            $cores = $finder->find();
            if (null !== $cores) {
                return [$finder, $cores];
            }
        }
        throw NumberOfCpuCoreNotFound::create();
    }
}
