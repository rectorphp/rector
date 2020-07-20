<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Rector\Core\Set\SetResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symplify\SetConfigResolver\ConfigResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorConfigsResolver
{
    /**
     * @var SetResolver
     */
    private $setResolver;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    public function __construct()
    {
        $this->setResolver = new SetResolver();
        $this->configResolver = new ConfigResolver();
    }

    /**
     * @noRector
     */
    public function getFirstResolvedConfig(): ?string
    {
        return $this->configResolver->getFirstResolvedConfig();
    }

    /**
     * @param SmartFileInfo[] $configFileInfos
     * @return SmartFileInfo[]
     */
    public function resolveSetFileInfosFromConfigFileInfos(array $configFileInfos): array
    {
        $setConfigFileInfos = [];

        foreach ($configFileInfos as $configFileInfo) {
            $setNames = $this->resolveSetsParameterFromConfigFile($configFileInfo);
            foreach ($setNames as $setName) {
                $setConfigFileInfos[] = $this->setResolver->resolveSetFileInfoByName($setName);
            }
        }

        return $setConfigFileInfos;
    }

    /**
     * @return SmartFileInfo[]
     * @noRector
     */
    public function provide(): array
    {
        $configFileInfos = [];

        // Detect configuration from --set
        $argvInput = new ArgvInput();

        $set = $this->setResolver->resolveSetFromInput($argvInput);
        if ($set !== null) {
            $configFileInfos[] = $set->getFileInfo();
        }

        // And from --config or default one
        $inputOrFallbackConfig = $this->configResolver->resolveFromInputWithFallback(
            $argvInput,
            ['rector.php', 'rector.yml', 'rector.yaml']
        );

        if ($inputOrFallbackConfig !== null) {
            $configFileInfos[] = new SmartFileInfo($inputOrFallbackConfig);
        }

        $setFileInfos = $this->resolveSetFileInfosFromConfigFileInfos($configFileInfos);

        return array_merge($configFileInfos, $setFileInfos);
    }

    /**
     * @return string[]
     */
    private function resolveSetsParameterFromConfigFile(SmartFileInfo $configFileInfo): array
    {
        $containerBuilder = new ContainerBuilder();
        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator());
        $phpFileLoader->load($configFileInfo->getRealPath());

        if (! $containerBuilder->hasParameter(Option::SETS)) {
            return [];
        }

        return $containerBuilder->getParameter(Option::SETS);
    }
}
