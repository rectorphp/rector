<?php

declare (strict_types=1);
namespace Rector\Parallel;

use RectorPrefix202610\Fidry\CpuCoreCounter\CpuCoreCounter;
final class CpuCoreCountProvider
{
    public function provide(): int
    {
        // getAvailableForParallelisation() respects cgroup/CFS quota (docker --cpus, KUBERNETES_CPU_LIMIT),
        // unlike getCount() which reports host cores and overcommits workers in containers
        return (new CpuCoreCounter())->getAvailableForParallelisation()->availableCpus;
    }
}
