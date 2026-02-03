<?php

declare (strict_types=1);
namespace Rector\Bootstrap;

use Rector\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix202602\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202602\Webmozart\Assert\Assert;
final class RectorConfigsResolver
{
    /**
     * @var string
     */
    public const DEFAULT_CONFIG_FILE = 'rector.php';
    /**
     * @var string
     */
    public const DEFAULT_DIST_CONFIG_FILE = 'rector.dist.php';
    public function provide(): BootstrapConfigs
    {
        $argvInput = new ArgvInput();
        $mainConfigFile = $this->resolveFromInputWithFallback($argvInput);
        return new BootstrapConfigs($mainConfigFile, []);
    }
    private function resolveFromInput(ArgvInput $argvInput): ?string
    {
        $configFile = $this->getOptionValue($argvInput, ['--config', '-c']);
        if ($configFile === null) {
            return null;
        }
        Assert::fileExists($configFile);
        return realpath($configFile);
    }
    private function resolveFromInputWithFallback(ArgvInput $argvInput): ?string
    {
        $configFile = $this->resolveFromInput($argvInput);
        if ($configFile !== null) {
            return $configFile;
        }
        // Try rector.php first, then fall back to rector.dist.php
        $rectorConfigFile = $this->createFallbackFileInfoIfFound(self::DEFAULT_CONFIG_FILE);
        if ($rectorConfigFile !== null) {
            return $rectorConfigFile;
        }
        return $this->createFallbackFileInfoIfFound(self::DEFAULT_DIST_CONFIG_FILE);
    }
    private function createFallbackFileInfoIfFound(string $fallbackFile): ?string
    {
        $rootFallbackFile = getcwd() . \DIRECTORY_SEPARATOR . $fallbackFile;
        if (!is_file($rootFallbackFile)) {
            return null;
        }
        return $rootFallbackFile;
    }
    /**
     * @param string[] $optionNames
     */
    private function getOptionValue(ArgvInput $argvInput, array $optionNames): ?string
    {
        foreach ($optionNames as $optionName) {
            if ($argvInput->hasParameterOption($optionName, \true)) {
                return $argvInput->getParameterOption($optionName, null, \true);
            }
        }
        return null;
    }
}
