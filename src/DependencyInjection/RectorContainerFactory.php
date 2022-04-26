<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection;

use Nette\Utils\FileSystem;
use Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\Stubs\PHPStanStubLoader;
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
                    'Your "%s" config is using old syntax with "ContainerConfigurator".%sPlease upgrade to "RectorConfig" that allows better autocomplete and future standard.',
                    $mainConfigFile,
                    PHP_EOL,
                );
                $symfonyStyle->warning($warningMessage);
                // to make message noticable
                sleep(1);
            }

            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->get(ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFile);
        }

        return $container;
    }

    /**
     * @param string[] $configFiles
     * @api
     */
    private function createFromConfigs(array $configFiles): ContainerInterface
    {
        $phpStanStubLoader = new PHPStanStubLoader();
        $phpStanStubLoader->loadStubs();

        $rectorKernel = new RectorKernel();
        return $rectorKernel->createFromConfigs($configFiles);
    }
}
