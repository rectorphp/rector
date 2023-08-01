<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix202308\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202308\Webmozart\Assert\Assert;
final class RectorConfigsResolver
{
    public function provide() : BootstrapConfigs
    {
        $argvInput = new ArgvInput();
        $mainConfigFile = $this->resolveFromInputWithFallback($argvInput, 'rector.php');
        $rectorRecipeConfigFile = $this->resolveRectorRecipeConfig($argvInput);
        $configFiles = [];
        if ($rectorRecipeConfigFile !== null) {
            $configFiles[] = $rectorRecipeConfigFile;
        }
        return new BootstrapConfigs($mainConfigFile, $configFiles);
    }
    private function resolveRectorRecipeConfig(ArgvInput $argvInput) : ?string
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
    private function resolveFromInput(ArgvInput $argvInput) : ?string
    {
        $configFile = $this->getOptionValue($argvInput, ['--config', '-c']);
        if ($configFile === null) {
            return null;
        }
        Assert::fileExists($configFile);
        return \realpath($configFile);
    }
    private function resolveFromInputWithFallback(ArgvInput $argvInput, string $fallbackFile) : ?string
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
    private function getOptionValue(ArgvInput $argvInput, array $optionNames) : ?string
    {
        foreach ($optionNames as $optionName) {
            if ($argvInput->hasParameterOption($optionName, \true)) {
                return $argvInput->getParameterOption($optionName, null, \true);
            }
        }
        return null;
    }
}
