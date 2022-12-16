<?php

declare (strict_types=1);
namespace RectorPrefix202212\Symplify\EasyParallel;

use RectorPrefix202212\Fidry\CpuCoreCounter\CpuCoreCounter;
use RectorPrefix202212\Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
final class CpuCoreCountProvider
{
    /**
     * @var int
     */
    private const DEFAULT_CORE_COUNT = 2;
    public function provide() : int
    {
        try {
            return (new CpuCoreCounter())->getCount();
        } catch (NumberOfCpuCoreNotFound $exception) {
            return self::DEFAULT_CORE_COUNT;
        }
    }
}
