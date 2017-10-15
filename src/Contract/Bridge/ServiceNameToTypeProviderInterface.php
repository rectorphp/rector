<?php declare(strict_types=1);

namespace Rector\Contract\Bridge;

interface ServiceNameToTypeProviderInterface
{
    /**
     * @return string[]
     */
    public function provide(): array;
}
