<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use Rector\RectorInstaller\GeneratedConfig;
use ReflectionClass;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
final class ExtensionConfigResolver
{
    /**
     * @return SmartFileInfo[]
     */
    public function provide() : array
    {
        $configFileInfos = [];
        if (!\class_exists('Rector\\RectorInstaller\\GeneratedConfig')) {
            return $configFileInfos;
        }
        $generatedConfigReflectionClass = new ReflectionClass('Rector\\RectorInstaller\\GeneratedConfig');
        if ($generatedConfigReflectionClass->getFileName() === \false) {
            return $configFileInfos;
        }
        $generatedConfigDirectory = \dirname($generatedConfigReflectionClass->getFileName());
        foreach (GeneratedConfig::EXTENSIONS as $extensionConfig) {
            /** @var string[] $includedFiles */
            $includedFiles = $extensionConfig['extra']['includes'] ?? [];
            foreach ($includedFiles as $includedFile) {
                $includedFilePath = $this->resolveIncludeFilePath($extensionConfig, $generatedConfigDirectory, $includedFile);
                if ($includedFilePath === null) {
                    /** @var string $installPath */
                    $installPath = $extensionConfig['install_path'];
                    $includedFilePath = \sprintf('%s/%s', $installPath, $includedFile);
                }
                $configFileInfos[] = new SmartFileInfo($includedFilePath);
            }
        }
        return $configFileInfos;
    }
    /**
     * @param array<string, mixed> $extensionConfig
     */
    private function resolveIncludeFilePath(array $extensionConfig, string $generatedConfigDirectory, string $includedFile) : ?string
    {
        if (!isset($extensionConfig['relative_install_path'])) {
            return null;
        }
        $includedFilePath = \sprintf('%s/%s/%s', $generatedConfigDirectory, (string) $extensionConfig['relative_install_path'], $includedFile);
        if (!\file_exists($includedFilePath)) {
            return null;
        }
        if (!\is_readable($includedFilePath)) {
            return null;
        }
        return $includedFilePath;
    }
}
