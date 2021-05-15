<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorConfigsResolver
{
    public function provide(): BootstrapConfigs
    {
        $argvInput = new ArgvInput();
        $mainConfigFileInfo = $this->resolveFromInputWithFallback($argvInput, 'rector.php');

        $rectorRecipeConfigFileInfo = $this->resolveRectorRecipeConfig($argvInput);

        $configFileInfos = [];
        if ($rectorRecipeConfigFileInfo !== null) {
            $configFileInfos[] = $rectorRecipeConfigFileInfo;
        }

        return new BootstrapConfigs($mainConfigFileInfo, $configFileInfos);
    }

    private function resolveRectorRecipeConfig(ArgvInput $argvInput): ?SmartFileInfo
    {
        if ($argvInput->getFirstArgument() !== 'generate') {
            return null;
        }

        // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
        $rectorRecipeFilePath = getcwd() . '/rector-recipe.php';
        if (! file_exists($rectorRecipeFilePath)) {
            return null;
        }

        return new SmartFileInfo($rectorRecipeFilePath);
    }

    private function resolveFromInput(ArgvInput $argvInput): ?SmartFileInfo
    {
        $configValue = $this->getOptionValue($argvInput, ['config', 'c']);
        if ($configValue === null) {
            return null;
        }

        if (! file_exists($configValue)) {
            $message = sprintf('File "%s" was not found', $configValue);
            throw new FileNotFoundException($message);
        }

        return new SmartFileInfo($configValue);
    }

    private function resolveFromInputWithFallback(ArgvInput $argvInput, string $fallbackFile): ?SmartFileInfo
    {
        $configFileInfo = $this->resolveFromInput($argvInput);
        if ($configFileInfo !== null) {
            return $configFileInfo;
        }

        return $this->createFallbackFileInfoIfFound($fallbackFile);
    }

    private function createFallbackFileInfoIfFound(string $fallbackFile): ?SmartFileInfo
    {
        $rootFallbackFile = getcwd() . DIRECTORY_SEPARATOR . $fallbackFile;
        if (! is_file($rootFallbackFile)) {
            return null;
        }

        return new SmartFileInfo($rootFallbackFile);
    }

    /**
     * @param string[] $optionNames
     */
    private function getOptionValue(ArgvInput $argvInput, array $optionNames): ?string
    {
        foreach ($optionNames as $optionName) {
            if ($argvInput->hasParameterOption($optionName, true)) {
                return $argvInput->getParameterOption($optionName, null, true);
            }
        }

        return null;
    }
}
