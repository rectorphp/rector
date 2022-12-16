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
namespace RectorPrefix202212\Fidry\CpuCoreCounter\Finder;

use function filter_var;
use function is_int;
use function sprintf;
use const FILTER_VALIDATE_INT;
/**
 * The number of (logical) cores.
 *
 * @see https://github.com/infection/infection/blob/fbd8c44/src/Resource/Processor/CpuCoresCountProvider.php#L69-L82
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class NProcFinder extends ProcOpenBasedFinder
{
    /**
     * @var bool
     */
    private $all;
    /**
     * @param bool $all If disabled will give the number of cores available for the current process only.
     */
    public function __construct(bool $all = \true)
    {
        $this->all = $all;
    }
    public function toString() : string
    {
        return sprintf('NProcFinder(all=%s)', $this->all ? 'true' : 'false');
    }
    protected function getCommand() : string
    {
        return 'nproc' . ($this->all ? ' --all' : '') . ' 2>&1';
    }
    /**
     * @return positive-int|null
     */
    public static function countCpuCores(string $nproc) : ?int
    {
        $cpuCount = filter_var($nproc, FILTER_VALIDATE_INT);
        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
}
