<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver;

use RectorPrefix20210510\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210510\Symplify\SetConfigResolver\Console\Option\OptionName;
use RectorPrefix20210510\Symplify\SetConfigResolver\Console\OptionValueResolver;
use RectorPrefix20210510\Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
abstract class AbstractConfigResolver
{
    /**
     * @var OptionValueResolver
     */
    private $optionValueResolver;
    public function __construct()
    {
        $this->optionValueResolver = new OptionValueResolver();
    }
    public function resolveFromInput(InputInterface $input) : ?SmartFileInfo
    {
        $configValue = $this->optionValueResolver->getOptionValue($input, OptionName::CONFIG);
        if ($configValue !== null) {
            if (!\file_exists($configValue)) {
                $message = \sprintf('File "%s" was not found', $configValue);
                throw new FileNotFoundException($message);
            }
            return $this->createFileInfo($configValue);
        }
        return null;
    }
    /**
     * @param string[] $fallbackFiles
     */
    public function resolveFromInputWithFallback(InputInterface $input, array $fallbackFiles) : ?SmartFileInfo
    {
        $configFileInfo = $this->resolveFromInput($input);
        if ($configFileInfo !== null) {
            return $configFileInfo;
        }
        return $this->createFallbackFileInfoIfFound($fallbackFiles);
    }
    /**
     * @param string[] $fallbackFiles
     */
    private function createFallbackFileInfoIfFound(array $fallbackFiles) : ?SmartFileInfo
    {
        foreach ($fallbackFiles as $fallbackFile) {
            $rootFallbackFile = \getcwd() . \DIRECTORY_SEPARATOR . $fallbackFile;
            if (\is_file($rootFallbackFile)) {
                return $this->createFileInfo($rootFallbackFile);
            }
        }
        return null;
    }
    private function createFileInfo(string $configValue) : SmartFileInfo
    {
        return new SmartFileInfo($configValue);
    }
}
