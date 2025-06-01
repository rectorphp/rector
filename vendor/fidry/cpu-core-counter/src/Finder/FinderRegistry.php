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
namespace RectorPrefix202506\Fidry\CpuCoreCounter\Finder;

final class FinderRegistry
{
    /**
     * @return list<CpuCoreFinder> List of all the known finders with all their variants.
     */
    public static function getAllVariants() : array
    {
        return [new CpuInfoFinder(), new DummyCpuCoreFinder(1), new HwLogicalFinder(), new HwPhysicalFinder(), new LscpuLogicalFinder(), new LscpuPhysicalFinder(), new _NProcessorFinder(), new NProcessorFinder(), new NProcFinder(\true), new NProcFinder(\false), new NullCpuCoreFinder(), SkipOnOSFamilyFinder::forWindows(new DummyCpuCoreFinder(1)), OnlyOnOSFamilyFinder::forWindows(new DummyCpuCoreFinder(1)), new OnlyInPowerShellFinder(new CmiCmdletLogicalFinder()), new OnlyInPowerShellFinder(new CmiCmdletPhysicalFinder()), new WindowsRegistryLogicalFinder(), new WmicPhysicalFinder(), new WmicLogicalFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultLogicalFinders() : array
    {
        return [OnlyOnOSFamilyFinder::forWindows(new OnlyInPowerShellFinder(new CmiCmdletLogicalFinder())), OnlyOnOSFamilyFinder::forWindows(new WindowsRegistryLogicalFinder()), OnlyOnOSFamilyFinder::forWindows(new WmicLogicalFinder()), new NProcFinder(), new HwLogicalFinder(), new _NProcessorFinder(), new NProcessorFinder(), new LscpuLogicalFinder(), new CpuInfoFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultPhysicalFinders() : array
    {
        return [OnlyOnOSFamilyFinder::forWindows(new OnlyInPowerShellFinder(new CmiCmdletPhysicalFinder())), OnlyOnOSFamilyFinder::forWindows(new WmicPhysicalFinder()), new HwPhysicalFinder(), new LscpuPhysicalFinder()];
    }
    private function __construct()
    {
    }
}
