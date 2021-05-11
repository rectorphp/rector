<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\SetConfigResolver\ConfigResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorConfigsResolver
{
    private ConfigResolver $configResolver;

    /**
     * @var array<string, SmartFileInfo[]>
     */
    private array $resolvedConfigFileInfos = [];

    private SetConfigResolver $setConfigResolver;

    public function __construct()
    {
        $this->setConfigResolver = new SetConfigResolver();
        $this->configResolver = new ConfigResolver();
    }

    /**
     * @return SmartFileInfo[]
     */
    public function resolveFromConfigFileInfo(SmartFileInfo $configFileInfo): array
    {
        $hash = sha1_file($configFileInfo->getRealPath());
        if ($hash === false) {
            return [];
        }

        if (isset($this->resolvedConfigFileInfos[$hash])) {
            return $this->resolvedConfigFileInfos[$hash];
        }

        $setFileInfos = $this->setConfigResolver->resolve($configFileInfo);
        $configFileInfos = array_merge([$configFileInfo], $setFileInfos);

        $this->resolvedConfigFileInfos[$hash] = $configFileInfos;

        return $configFileInfos;
    }

    public function provide(): BootstrapConfigs
    {
        $argvInput = new ArgvInput();
        $mainConfigFileInfo = $this->configResolver->resolveFromInputWithFallback($argvInput, ['rector.php']);

        $configFileInfos = $mainConfigFileInfo instanceof SmartFileInfo ? $this->resolveFromConfigFileInfo(
            $mainConfigFileInfo
        ) : [];

        $configFileInfos = $this->appendRectorRecipeConfig($argvInput, $configFileInfos);

        return new BootstrapConfigs($mainConfigFileInfo, $configFileInfos);
    }

    /**
     * @param SmartFileInfo[] $configFileInfos
     * @return SmartFileInfo[]
     */
    private function appendRectorRecipeConfig(ArgvInput $argvInput, array $configFileInfos): array
    {
        if ($argvInput->getFirstArgument() !== 'generate') {
            return $configFileInfos;
        }

        // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
        $rectorRecipeFilePath = getcwd() . '/rector-recipe.php';
        if (file_exists($rectorRecipeFilePath)) {
            $configFileInfos[] = new SmartFileInfo($rectorRecipeFilePath);
        }

        return $configFileInfos;
    }
}
