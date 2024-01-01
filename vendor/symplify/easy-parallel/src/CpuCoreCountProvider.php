<?php

declare (strict_types=1);
namespace RectorPrefix202401\Symplify\EasyParallel;

use RectorPrefix202401\Fidry\CpuCoreCounter\CpuCoreCounter;
use RectorPrefix202401\Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
/**
 * @api
 */
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
