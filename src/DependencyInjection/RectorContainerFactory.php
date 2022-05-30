<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection;

use Nette\Utils\FileSystem;
use Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RectorContainerFactory
{
    public function createFromBootstrapConfigs(BootstrapConfigs $bootstrapConfigs): ContainerInterface
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFiles());

        $mainConfigFile = $bootstrapConfigs->getMainConfigFile();

        if ($mainConfigFile !== null) {
            // warning about old syntax before RectorConfig
            $fileContents = FileSystem::read($mainConfigFile);
            if (str_contains($fileContents, 'ContainerConfigurator $containerConfigurator')) {
                /** @var SymfonyStyle $symfonyStyle */
                $symfonyStyle = $container->get(SymfonyStyle::class);

                // @todo add link to blog post after release
                $warningMessage = sprintf(
                    'Your "%s" config is using old syntax with "ContainerConfigurator".%sUpgrade to "RectorConfig" that allows better autocomplete and future standard.',
                    $mainConfigFile,
                    PHP_EOL,
                );
                $symfonyStyle->error($warningMessage);
                // to make message noticable
                sleep(10);
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
    private function createFromConfigs(array $configFiles): ContainerInterface
    {
        $rectorKernel = new RectorKernel();
        return $rectorKernel->createFromConfigs($configFiles);
    }
}
