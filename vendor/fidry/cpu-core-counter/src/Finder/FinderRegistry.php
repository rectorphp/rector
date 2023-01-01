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
namespace RectorPrefix202301\Fidry\CpuCoreCounter\Finder;

final class FinderRegistry
{
    /**
     * @return list<CpuCoreFinder> List of all the known finders with all their variants.
     */
    public static function getAllVariants() : array
    {
        return [new CpuInfoFinder(), new DummyCpuCoreFinder(1), new HwLogicalFinder(), new HwPhysicalFinder(), new _NProcessorFinder(), new NProcessorFinder(), new NProcFinder(\true), new NProcFinder(\false), new NullCpuCoreFinder(), new OnlyOnWindowsFinder(new DummyCpuCoreFinder(1)), new SkipOnWindowsFinder(new DummyCpuCoreFinder(1)), new WmicPhysicalFinder(), new WmicLogicalFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultLogicalFinders() : array
    {
        return [new OnlyOnWindowsFinder(new WmicLogicalFinder()), new NProcFinder(), new HwLogicalFinder(), new _NProcessorFinder(), new NProcessorFinder(), new CpuInfoFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultPhysicalFinders() : array
    {
        return [new OnlyOnWindowsFinder(new WmicPhysicalFinder()), new HwPhysicalFinder()];
    }
    private function __construct()
    {
    }
}
