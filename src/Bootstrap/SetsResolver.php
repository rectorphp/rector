<?php

declare(strict_types=1);

namespace Rector\Bootstrap;

use Symfony\Component\Yaml\Yaml;

final class SetsResolver
{
    /**
     * @param string[] $configFiles
     * @return string[]
     */
    public function resolveFromConfigFiles(array $configFiles): array
    {
        $sets = [];

        foreach ($configFiles as $configFile) {
            $configContent = Yaml::parseFile($configFile);
            $sets += $configContent['parameters']['sets'] ?? [];
        }

        return $sets;
    }
}
