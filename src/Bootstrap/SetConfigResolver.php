<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetConfigResolver
{
    /**
     * @var string
     */
    private const SETS = 'sets';

    /**
     * @return SmartFileInfo[]
     */
    public function resolve(SmartFileInfo $smartFileInfo): array
    {
        $containerBuilder = new ContainerBuilder();

        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator());
        $phpFileLoader->load($smartFileInfo->getRealPath());

        if (! $containerBuilder->hasParameter(self::SETS)) {
            return [];
        }

        $sets = (array) $containerBuilder->getParameter(self::SETS);
        return $this->wrapToFileInfos($sets);
    }

    /**
     * @param string[] $sets
     * @return SmartFileInfo[]
     */
    private function wrapToFileInfos(array $sets): array
    {
        $setFileInfos = [];
        foreach ($sets as $set) {
            $setFileInfos[] = new SmartFileInfo($set);
        }

        return $setFileInfos;
    }
}
