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
namespace RectorPrefix202409\Fidry\CpuCoreCounter\Finder;

use RectorPrefix202409\Fidry\CpuCoreCounter\Executor\ProcessExecutor;
use RectorPrefix202409\Fidry\CpuCoreCounter\Executor\ProcOpenExecutor;
use function filter_var;
use function function_exists;
use function is_int;
use function sprintf;
use function trim;
use const FILTER_VALIDATE_INT;
use const PHP_EOL;
abstract class ProcOpenBasedFinder implements CpuCoreFinder
{
    /**
     * @var ProcessExecutor
     */
    private $executor;
    public function __construct(?ProcessExecutor $executor = null)
    {
        $this->executor = $executor ?? new ProcOpenExecutor();
    }
    public function diagnose() : string
    {
        if (!function_exists('proc_open')) {
            return 'The function "proc_open" is not available.';
        }
        $command = $this->getCommand();
        $output = $this->executor->execute($command);
        if (null === $output) {
            return sprintf('Failed to execute the command "%s".', $command);
        }
        [$stdout, $stderr] = $output;
        $failed = '' !== trim($stderr);
        return $failed ? sprintf('Executed the command "%s" which wrote the following output to the STDERR:%s%s%sWill return "null".', $command, PHP_EOL, $stderr, PHP_EOL) : sprintf('Executed the command "%s" and got the following (STDOUT) output:%s%s%sWill return "%s".', $command, PHP_EOL, $stdout, PHP_EOL, $this->countCpuCores($stdout) ?? 'null');
    }
    /**
     * @return positive-int|null
     */
    public function find() : ?int
    {
        $output = $this->executor->execute($this->getCommand());
        if (null === $output) {
            return null;
        }
        [$stdout, $stderr] = $output;
        $failed = '' !== trim($stderr);
        return $failed ? null : $this->countCpuCores($stdout);
    }
    /**
     * @internal
     *
     * @return positive-int|null
     */
    protected function countCpuCores(string $process) : ?int
    {
        $cpuCount = filter_var($process, FILTER_VALIDATE_INT);
        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
    protected abstract function getCommand() : string;
}
