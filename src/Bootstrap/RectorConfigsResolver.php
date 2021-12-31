<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix20211231\Symplify\SmartFileSystem\Exception\FileNotFoundException;
final class RectorConfigsResolver
{
    public function provide() : \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs
    {
        $argvInput = new \RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput();
        $mainConfigFile = $this->resolveFromInputWithFallback($argvInput, 'rector.php');
        $rectorRecipeConfigFile = $this->resolveRectorRecipeConfig($argvInput);
        $configFiles = [];
        if ($rectorRecipeConfigFile !== null) {
            $configFiles[] = $rectorRecipeConfigFile;
        }
        return new \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs($mainConfigFile, $configFiles);
    }
    private function resolveRectorRecipeConfig(\RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput $argvInput) : ?string
    {
        if ($argvInput->getFirstArgument() !== 'generate') {
            return null;
        }
        // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
        $rectorRecipeFilePath = \getcwd() . '/rector-recipe.php';
        if (!\file_exists($rectorRecipeFilePath)) {
            return null;
        }
        return $rectorRecipeFilePath;
    }
    private function resolveFromInput(\RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput $argvInput) : ?string
    {
        $configFile = $this->getOptionValue($argvInput, ['--config', '-c']);
        if ($configFile === null) {
            return null;
        }
        if (!\file_exists($configFile)) {
            $message = \sprintf('File "%s" was not found', $configFile);
            throw new \RectorPrefix20211231\Symplify\SmartFileSystem\Exception\FileNotFoundException($message);
        }
        return \realpath($configFile);
    }
    private function resolveFromInputWithFallback(\RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput $argvInput, string $fallbackFile) : ?string
    {
        $configFile = $this->resolveFromInput($argvInput);
        if ($configFile !== null) {
            return $configFile;
        }
        return $this->createFallbackFileInfoIfFound($fallbackFile);
    }
    private function createFallbackFileInfoIfFound(string $fallbackFile) : ?string
    {
        $rootFallbackFile = \getcwd() . \DIRECTORY_SEPARATOR . $fallbackFile;
        if (!\is_file($rootFallbackFile)) {
            return null;
        }
        return $rootFallbackFile;
    }
    /**
     * @param string[] $optionNames
     */
    private function getOptionValue(\RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput $argvInput, array $optionNames) : ?string
    {
        foreach ($optionNames as $optionName) {
            if ($argvInput->hasParameterOption($optionName, \true)) {
                return $argvInput->getParameterOption($optionName, null, \true);
            }
        }
        return null;
    }
}
