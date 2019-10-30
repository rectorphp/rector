<?php

declare(strict_types=1);

namespace Rector\Bootstrap;

use Rector\Set\Set;

final class ConfigResolver
{
    /**
     * @var SetOptionResolver
     */
    private $setOptionResolver;

    /**
     * @var SetsResolver
     */
    private $setsResolver;

    public function __construct()
    {
        $this->setOptionResolver = new SetOptionResolver();
        $this->setsResolver = new SetsResolver();
    }

    /**
     * @param string[] $configFiles
     * @return string[]
     */
    public function resolveFromParameterSetsFromConfigFiles(array $configFiles): array
    {
        $configs = [];

        $sets = $this->setsResolver->resolveFromConfigFiles($configFiles);
        return array_merge($configs, $this->resolveFromSets($sets));
    }

    /**
     * @param string[] $sets
     * @return string[]
     */
    private function resolveFromSets(array $sets): array
    {
        $configs = [];
        foreach ($sets as $set) {
            $configs[] = $this->setOptionResolver->detectFromNameAndDirectory($set, Set::SET_DIRECTORY);
        }

        return $configs;
    }
}
