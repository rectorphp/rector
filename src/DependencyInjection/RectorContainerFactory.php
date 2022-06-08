<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use RectorPrefix20220608\Nette\Utils\FileSystem;
use RectorPrefix20220608\Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix20220608\Symfony\Component\Console\Style\SymfonyStyle;
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
                /** @var SymfonyStyle $symfonyStyle */
                $symfonyStyle = $container->get(SymfonyStyle::class);
                $warningMessage = \sprintf('Your "%s" config is using old syntax with "ContainerConfigurator".%sUpgrade to "RectorConfig" that allows better autocomplete and future standard: https://getrector.org/blog/new-in-rector-012-introducing-rector-config-with-autocomplete', $mainConfigFile, \PHP_EOL);
                $symfonyStyle->error($warningMessage);
                // to make message noticable
                \sleep(10);
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
