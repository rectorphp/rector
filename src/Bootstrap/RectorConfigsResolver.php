<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix20211020\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix20211020\Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
final class RectorConfigsResolver
{
    public function provide() : \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs
    {
        $argvInput = new \RectorPrefix20211020\Symfony\Component\Console\Input\ArgvInput();
        $mainConfigFileInfo = $this->resolveFromInputWithFallback($argvInput, 'rector.php');
        $rectorRecipeConfigFileInfo = $this->resolveRectorRecipeConfig($argvInput);
        $configFileInfos = [];
        if ($rectorRecipeConfigFileInfo !== null) {
            $configFileInfos[] = $rectorRecipeConfigFileInfo;
        }
        return new \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs($mainConfigFileInfo, $configFileInfos);
    }
    private function resolveRectorRecipeConfig(\RectorPrefix20211020\Symfony\Component\Console\Input\ArgvInput $argvInput) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        if ($argvInput->getFirstArgument() !== 'generate') {
            return null;
        }
        // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
        $rectorRecipeFilePath = \getcwd() . '/rector-recipe.php';
        if (!\file_exists($rectorRecipeFilePath)) {
            return null;
        }
        return new \Symplify\SmartFileSystem\SmartFileInfo($rectorRecipeFilePath);
    }
    private function resolveFromInput(\RectorPrefix20211020\Symfony\Component\Console\Input\ArgvInput $argvInput) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        $configValue = $this->getOptionValue($argvInput, ['--config', '-c']);
        if ($configValue === null) {
            return null;
        }
        if (!\file_exists($configValue)) {
            $message = \sprintf('File "%s" was not found', $configValue);
            throw new \RectorPrefix20211020\Symplify\SmartFileSystem\Exception\FileNotFoundException($message);
        }
        return new \Symplify\SmartFileSystem\SmartFileInfo($configValue);
    }
    private function resolveFromInputWithFallback(\RectorPrefix20211020\Symfony\Component\Console\Input\ArgvInput $argvInput, string $fallbackFile) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        $configFileInfo = $this->resolveFromInput($argvInput);
        if ($configFileInfo !== null) {
            return $configFileInfo;
        }
        return $this->createFallbackFileInfoIfFound($fallbackFile);
    }
    private function createFallbackFileInfoIfFound(string $fallbackFile) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        $rootFallbackFile = \getcwd() . \DIRECTORY_SEPARATOR . $fallbackFile;
        if (!\is_file($rootFallbackFile)) {
            return null;
        }
        return new \Symplify\SmartFileSystem\SmartFileInfo($rootFallbackFile);
    }
    /**
     * @param string[] $optionNames
     */
    private function getOptionValue(\RectorPrefix20211020\Symfony\Component\Console\Input\ArgvInput $argvInput, array $optionNames) : ?string
    {
        foreach ($optionNames as $optionName) {
            if ($argvInput->hasParameterOption($optionName, \true)) {
                return $argvInput->getParameterOption($optionName, null, \true);
            }
        }
        return null;
    }
}
