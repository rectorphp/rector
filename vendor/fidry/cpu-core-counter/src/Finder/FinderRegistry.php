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
namespace RectorPrefix202609\Fidry\CpuCoreCounter\Finder;

final class FinderRegistry
{
    /**
     * @return list<CpuCoreFinder> List of all the known finders with all their variants.
     */
    public static function getAllVariants(): array
    {
        return [new CgroupCpuQuotaFinder(), new CpuAffinityFinder(), new CpuInfoFinder(), new CpuInfoPhysicalFinder(), new DummyCpuCoreFinder(1), new EnvVariableFinder('NUMBER_OF_PROCESSORS'), new HwLogicalFinder(), new HwPhysicalFinder(), new LscpuLogicalFinder(), new LscpuPhysicalFinder(), new LscpuRawLogicalFinder(), new LscpuRawPhysicalFinder(), new _NProcessorFinder(), new NProcessorFinder(), new NProcFinder(\true), new NProcFinder(\false), new NullCpuCoreFinder(), SkipOnOSFamilyFinder::forWindows(new DummyCpuCoreFinder(1)), OnlyOnOSFamilyFinder::forWindows(new DummyCpuCoreFinder(1)), new CmiCmdletLogicalFinder(), new CmiCmdletPhysicalFinder(), new WindowsRegistryLogicalFinder(), new WmicPhysicalFinder(), new WmicLogicalFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultLogicalFinders(): array
    {
        return [
            OnlyOnOSFamilyFinder::forWindows(new WindowsRegistryLogicalFinder()),
            OnlyOnOSFamilyFinder::forWindows(new CmiCmdletLogicalFinder()),
            OnlyOnOSFamilyFinder::forWindows(new WmicLogicalFinder()),
            // Keep after the other Windows finders: any parent process can
            // override it, and before Windows 11 22H2 it may count only the
            // processor group of the process.
            OnlyOnOSFamilyFinder::forWindows(new EnvVariableFinder('NUMBER_OF_PROCESSORS')),
            // Keep before the finders that ignore pinned CPUs, e.g. getconf with glibc.
            new CpuAffinityFinder(),
            new NProcFinder(),
            new HwLogicalFinder(),
            new _NProcessorFinder(),
            new NProcessorFinder(),
            new LscpuLogicalFinder(),
            new LscpuRawLogicalFinder(),
            new CpuInfoFinder(),
        ];
    }
    /**
     * Inside a virtual machine, these finders count the cores of the CPU
     * topology presented by the hypervisor, not the host's physical cores. As a
     * VM cannot be reliably detected on every platform, the library does not
     * attempt to: it is up to you whether to trust this count in a VM.
     * Inside a container, they count all of the host's physical cores,
     * including those the container may not use.
     *
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultPhysicalFinders(): array
    {
        return [OnlyOnOSFamilyFinder::forWindows(new CmiCmdletPhysicalFinder()), OnlyOnOSFamilyFinder::forWindows(new WmicPhysicalFinder()), new HwPhysicalFinder(), new LscpuPhysicalFinder(), new LscpuRawPhysicalFinder(), new CpuInfoPhysicalFinder()];
    }
    /**
     * @return CpuCoreFinder Finds the maximum number of cores to use, rather than the
     *                       number of cores. CpuCoreCounter uses it when no count
     *                       limit is given to getAvailableForParallelisation().
     */
    public static function getDefaultCountLimitFinder(): CpuCoreFinder
    {
        return new FirstCpuCoreFinder(new CgroupCpuQuotaFinder(), new EnvVariableFinder('KUBERNETES_CPU_LIMIT'));
    }
    private function __construct()
    {
    }
}
