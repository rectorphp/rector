<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Utils\ProjectValidator\Exception\CouldNotDeterminedCpuCoresException;

final class CpuCoreCountResolver
{
    /**
     * @see https://gist.github.com/divinity76/01ef9ca99c111565a72d3a8a6e42f7fb
     *
     * returns number of cpu cores
     * Copyleft 2018, license: WTFPL
     */
    public function resolve(): int
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $str = trim(shell_exec('wmic cpu get NumberOfCores 2>&1'));

            $matches = Strings::match($str, '#(\d+)#');
            if (! $matches) {
                throw new CouldNotDeterminedCpuCoresException('wmic failed to get number of cpu cores on windows!');
            }

            return (int) $matches[1];
        }

        $ret = @shell_exec('nproc');
        if (is_string($ret)) {
            $ret = trim($ret);
            if (($tmp = filter_var($ret, FILTER_VALIDATE_INT)) !== false) {
                return (int) $tmp;
            }
        }

        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = FileSystem::read('/proc/cpuinfo');
            $count = substr_count($cpuinfo, 'processor');
            if ($count > 0) {
                return $count;
            }
        }

        throw new CouldNotDeterminedCpuCoresException('Failed to detect number of CPUs');
    }
}
