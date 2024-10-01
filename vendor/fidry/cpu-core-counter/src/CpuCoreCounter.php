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
namespace RectorPrefix202410\Fidry\CpuCoreCounter;

use RectorPrefix202410\Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use RectorPrefix202410\Fidry\CpuCoreCounter\Finder\EnvVariableFinder;
use RectorPrefix202410\Fidry\CpuCoreCounter\Finder\FinderRegistry;
use InvalidArgumentException;
use function implode;
use function max;
use function sprintf;
use function sys_getloadavg;
use const PHP_EOL;
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
     * @param positive-int|0    $reservedCpus      Number of CPUs to reserve. This is useful when you want
     *                                             to reserve some CPUs for other processes. If the main
     *                                             process is going to be busy still, you may want to set
     *                                             this value to 1.
     * @param non-zero-int|null $countLimit        The maximum number of CPUs to return. If not provided, it
     *                                             may look for a limit in the environment variables, e.g.
     *                                             KUBERNETES_CPU_LIMIT. If negative, the limit will be
     *                                             the total number of cores found minus the absolute value.
     *                                             For instance if the system has 10 cores and countLimit=-2,
     *                                             then the effective limit considered will be 8.
     * @param float|null        $loadLimit         Element of [0., 1.]. Percentage representing the
     *                                             amount of cores that should be used among the available
     *                                             resources. For instance, if set to 0.7, it will use 70%
     *                                             of the available cores, i.e. if 1 core is reserved, 11
     *                                             cores are available and 5 are busy, it will use 70%
     *                                             of (11-1-5)=5 cores, so 3 cores. Set this parameter to null
     *                                             to skip this check. Beware that 1 does not mean "no limit",
     *                                             but 100% of the _available_ resources, i.e. with the
     *                                             previous example, it will return 5 cores. How busy is
     *                                             the system is determined by the system load average
     *                                             (see $systemLoadAverage).
     * @param float|null        $systemLoadAverage The system load average. If passed, it will use
     *                                             this information to limit the available cores based
     *                                             on the _available_ resources. For instance, if there
     *                                             is 10 cores but 3 are busy, then only 7 cores will
     *                                             be considered for further calculation. If set to
     *                                             `null`, it will use `sys_getloadavg()` to check the
     *                                             load of the system in the past minute. You can
     *                                             otherwise pass an arbitrary value. Should be a
     *                                             positive float.
     *
     * @see https://php.net/manual/en/function.sys-getloadavg.php
     */
    public function getAvailableForParallelisation(int $reservedCpus = 0, ?int $countLimit = null, ?float $loadLimit = null, ?float $systemLoadAverage = 0.0) : ParallelisationResult
    {
        self::checkCountLimit($countLimit);
        self::checkLoadLimit($loadLimit);
        self::checkSystemLoadAverage($systemLoadAverage);
        $totalCoreCount = $this->getCountWithFallback(1);
        $availableCores = max(1, $totalCoreCount - $reservedCpus);
        // Adjust available CPUs based on current load
        if (null !== $loadLimit) {
            $correctedSystemLoadAverage = null === $systemLoadAverage ? sys_getloadavg()[0] ?? 0.0 : $systemLoadAverage;
            $availableCores = max(1, $loadLimit * ($availableCores - $correctedSystemLoadAverage));
        }
        if (null === $countLimit) {
            $correctedCountLimit = self::getKubernetesLimit();
        } else {
            $correctedCountLimit = $countLimit > 0 ? $countLimit : max(1, $totalCoreCount + $countLimit);
        }
        if (null !== $correctedCountLimit && $availableCores > $correctedCountLimit) {
            $availableCores = $correctedCountLimit;
        }
        return new ParallelisationResult($reservedCpus, $countLimit, $loadLimit, $systemLoadAverage, $correctedCountLimit, $correctedSystemLoadAverage ?? $systemLoadAverage, $totalCoreCount, (int) $availableCores);
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
     * @param positive-int $fallback
     *
     * @return positive-int
     */
    public function getCountWithFallback(int $fallback) : int
    {
        try {
            return $this->getCount();
        } catch (NumberOfCpuCoreNotFound $exception) {
            return $fallback;
        }
    }
    /**
     * This method is mostly for debugging purposes.
     */
    public function trace() : string
    {
        $output = [];
        foreach ($this->finders as $finder) {
            $output[] = sprintf('Executing the finder "%s":', $finder->toString());
            $output[] = $finder->diagnose();
            $cores = $finder->find();
            if (null !== $cores) {
                $output[] = 'Result found: ' . $cores;
                break;
            }
            $output[] = '–––';
        }
        return implode(PHP_EOL, $output);
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
    /**
     * @return positive-int|null
     */
    public static function getKubernetesLimit() : ?int
    {
        $finder = new EnvVariableFinder('KUBERNETES_CPU_LIMIT');
        return $finder->find();
    }
    private static function checkCountLimit(?int $countLimit) : void
    {
        if (0 === $countLimit) {
            throw new InvalidArgumentException('The count limit must be a non zero integer. Got "0".');
        }
    }
    private static function checkLoadLimit(?float $loadLimit) : void
    {
        if (null === $loadLimit) {
            return;
        }
        if ($loadLimit < 0.0 || $loadLimit > 1.0) {
            throw new InvalidArgumentException(sprintf('The load limit must be in the range [0., 1.], got "%s".', $loadLimit));
        }
    }
    private static function checkSystemLoadAverage(?float $systemLoadAverage) : void
    {
        if (null !== $systemLoadAverage && $systemLoadAverage < 0.0) {
            throw new InvalidArgumentException(sprintf('The system load average must be a positive float, got "%s".', $systemLoadAverage));
        }
    }
}
