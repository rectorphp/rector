<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix20210514\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix20210514\Symplify\SetConfigResolver\ConfigResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class RectorConfigsResolver
{
    /**
     * @var \Symplify\SetConfigResolver\ConfigResolver
     */
    private $configResolver;
    /**
     * @var array<string, SmartFileInfo[]>
     */
    private $resolvedConfigFileInfos = [];
    /**
     * @var \Rector\Core\Bootstrap\SetConfigResolver
     */
    private $setConfigResolver;
    public function __construct()
    {
        $this->setConfigResolver = new \Rector\Core\Bootstrap\SetConfigResolver();
        $this->configResolver = new \RectorPrefix20210514\Symplify\SetConfigResolver\ConfigResolver();
    }
    /**
     * @return SmartFileInfo[]
     */
    public function resolveFromConfigFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $configFileInfo) : array
    {
        $hash = \sha1_file($configFileInfo->getRealPath());
        if ($hash === \false) {
            return [];
        }
        if (isset($this->resolvedConfigFileInfos[$hash])) {
            return $this->resolvedConfigFileInfos[$hash];
        }
        $setFileInfos = $this->setConfigResolver->resolve($configFileInfo);
        $configFileInfos = \array_merge([$configFileInfo], $setFileInfos);
        $this->resolvedConfigFileInfos[$hash] = $configFileInfos;
        return $configFileInfos;
    }
    public function provide() : \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs
    {
        $argvInput = new \RectorPrefix20210514\Symfony\Component\Console\Input\ArgvInput();
        $mainConfigFileInfo = $this->configResolver->resolveFromInputWithFallback($argvInput, ['rector.php']);
        $configFileInfos = $mainConfigFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo ? $this->resolveFromConfigFileInfo($mainConfigFileInfo) : [];
        $configFileInfos = $this->appendRectorRecipeConfig($argvInput, $configFileInfos);
        return new \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs($mainConfigFileInfo, $configFileInfos);
    }
    /**
     * @param SmartFileInfo[] $configFileInfos
     * @return SmartFileInfo[]
     */
    private function appendRectorRecipeConfig(\RectorPrefix20210514\Symfony\Component\Console\Input\ArgvInput $argvInput, array $configFileInfos) : array
    {
        if ($argvInput->getFirstArgument() !== 'generate') {
            return $configFileInfos;
        }
        // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
        $rectorRecipeFilePath = \getcwd() . '/rector-recipe.php';
        if (\file_exists($rectorRecipeFilePath)) {
            $configFileInfos[] = new \Symplify\SmartFileSystem\SmartFileInfo($rectorRecipeFilePath);
        }
        return $configFileInfos;
    }
}
