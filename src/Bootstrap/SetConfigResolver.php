<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use RectorPrefix20210514\Symfony\Component\Config\FileLocator;
use RectorPrefix20210514\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210514\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
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
    public function resolve(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $containerBuilder = new \RectorPrefix20210514\Symfony\Component\DependencyInjection\ContainerBuilder();
        $phpFileLoader = new \RectorPrefix20210514\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, new \RectorPrefix20210514\Symfony\Component\Config\FileLocator());
        $phpFileLoader->load($smartFileInfo->getRealPath());
        if (!$containerBuilder->hasParameter(self::SETS)) {
            return [];
        }
        $sets = (array) $containerBuilder->getParameter(self::SETS);
        return $this->wrapToFileInfos($sets);
    }
    /**
     * @param string[] $sets
     * @return SmartFileInfo[]
     */
    private function wrapToFileInfos(array $sets) : array
    {
        $setFileInfos = [];
        foreach ($sets as $set) {
            $setFileInfos[] = new \Symplify\SmartFileSystem\SmartFileInfo($set);
        }
        return $setFileInfos;
    }
}
