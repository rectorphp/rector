<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Symplify\EasyParallel;

use RectorPrefix20220531\Nette\Utils\Strings;
/**
 * From https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92
 */
final class CpuCoreCountProvider
{
    /**
     * @see https://regex101.com/r/XMeAl4/1
     * @var string
     */
    public const PROCESSOR_REGEX = '#^processor#m';
    /**
     * @var int
     */
    private const DEFAULT_CORE_COUNT = 2;
    public function provide() : int
    {
        // from brianium/paratest
        $cpuInfoCount = $this->resolveFromProcCpuinfo();
        if ($cpuInfoCount !== null) {
            return $cpuInfoCount;
        }
        $coreCount = self::DEFAULT_CORE_COUNT;
        if (\DIRECTORY_SEPARATOR === '\\') {
            // Windows
            $process = @\popen('wmic cpu get NumberOfCores', 'rb');
            if ($process !== \false) {
                \fgets($process);
                $coreCount = (int) \fgets($process);
                \pclose($process);
            }
            return $coreCount;
        }
        $process = @\popen('sysctl -n hw.ncpu', 'rb');
        if ($process !== \false) {
            $coreCount = (int) \fgets($process);
            \pclose($process);
        }
        return $coreCount;
    }
    /**
     * @return int|null
     */
    private function resolveFromProcCpuinfo()
    {
        if (!\is_file('/proc/cpuinfo')) {
            return null;
        }
        // Linux (and potentially Windows with linux sub systems)
        $cpuinfo = \file_get_contents('/proc/cpuinfo');
        if ($cpuinfo === \false) {
            return null;
        }
        $matches = \RectorPrefix20220531\Nette\Utils\Strings::matchAll($cpuinfo, self::PROCESSOR_REGEX);
        if ($matches === []) {
            return 0;
        }
        return \count($matches);
    }
}
