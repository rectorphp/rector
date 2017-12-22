<?php declare(strict_types=1);

namespace Rector\Configuration;

final class ConfigMerger
{
    /**
     * @todo resolve nicely, memory leak at the moment: https://travis-ci.org/rectorphp/rector/jobs/319947148#L641
     *
     * This magic will merge array recursively without making any extra duplications.
     *
     * Only array_merge doesn't work in this case.
     *
     * @param mixed[] $configs
     * @return mixed[]
     */
    public function mergeConfigs(array $configs): array
    {
        dump($configs);

        if (count($configs) <= 1) {
            return $configs[0];
        }

        $mergedConfigs = [];

        foreach ($configs as $config) {
            $mergedConfigs = array_merge(
                $mergedConfigs,
                $config,
                array_replace_recursive($mergedConfigs, $config)
            );
        }

        return $mergedConfigs;
    }
}
