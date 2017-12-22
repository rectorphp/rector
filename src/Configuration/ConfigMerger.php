<?php declare(strict_types=1);

namespace Rector\Configuration;

final class ConfigMerger
{
    /**
     * Merges arrays recursively without any duplications
     *
     * @param mixed[] $configs
     * @return mixed[]
     */
    public function mergeConfigs(array $configs): array
    {
        return array_replace_recursive(...$configs);
    }
}
