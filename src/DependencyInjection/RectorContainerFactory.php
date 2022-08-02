<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use RectorPrefix202208\Nette\Utils\FileSystem;
use RectorPrefix202208\Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Exception\DeprecatedException;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
final class RectorContainerFactory
{
    public function createFromBootstrapConfigs(BootstrapConfigs $bootstrapConfigs) : ContainerInterface
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFiles());
        $mainConfigFile = $bootstrapConfigs->getMainConfigFile();
        if ($mainConfigFile !== null) {
            // warning about old syntax before RectorConfig
            $fileContents = FileSystem::read($mainConfigFile);
            if (\strpos($fileContents, 'ContainerConfigurator $containerConfigurator') !== \false) {
                $warningMessage = \sprintf('Your "%s" config uses deprecated syntax with "ContainerConfigurator".%sUpgrade to "RectorConfig": https://getrector.org/blog/new-in-rector-012-introducing-rector-config-with-autocomplete', $mainConfigFile, \PHP_EOL);
                throw new DeprecatedException($warningMessage);
            }
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->get(ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFile);
        }
        /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
        $bootstrapFilesIncluder = $container->get(BootstrapFilesIncluder::class);
        $bootstrapFilesIncluder->includeBootstrapFiles();
        return $container;
    }
    /**
     * @param string[] $configFiles
     * @api
     */
    private function createFromConfigs(array $configFiles) : ContainerInterface
    {
        $rectorKernel = new RectorKernel();
        return $rectorKernel->createFromConfigs($configFiles);
    }
}
