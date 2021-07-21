<?php

declare(strict_types=1);

namespace Rector\VersionBonding;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;

final class PhpVersionedFilter
{
    public function __construct(
        private PhpVersionProvider $phpVersionProvider
    ) {
    }

    /**
     * @template T as RectorInterface
     * @param array<T> $rectors
     * @return array<T>
     */
    public function filter(array $rectors): array
    {
        $minProjectPhpVersion = $this->phpVersionProvider->provide();

        $activeRectors = [];
        foreach ($rectors as $rector) {
            if (! $rector instanceof MinPhpVersionInterface) {
                $activeRectors[] = $rector;
                continue;
            }

            // does satify version? → include
            if ($rector->provideMinPhpVersion() <= $minProjectPhpVersion) {
                $activeRectors[] = $rector;
            }
        }

        return $activeRectors;
    }
}
