<?php

declare (strict_types=1);
namespace Rector\Bootstrap;

use Rector\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix202410\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202410\Webmozart\Assert\Assert;
final class RectorConfigsResolver
{
    public function provide() : BootstrapConfigs
    {
        $argvInput = new ArgvInput();
        $mainConfigFile = $this->resolveFromInputWithFallback($argvInput, 'rector.php');
        return new BootstrapConfigs($mainConfigFile, []);
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
