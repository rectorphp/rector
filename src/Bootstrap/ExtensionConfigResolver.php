<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Rector\RectorInstaller\GeneratedConfig;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExtensionConfigResolver
{
    /**
     * @var array<int, string>
     */
    private const DEFAULT_EXTENSIONS = [
        'rector-symfony',
        'rector-nette',
        'rector-laravel',
        'rector-phpunit',
        'rector-cakephp',
        'rector-doctrine',
    ];

    /**
     * @var array<string, SmartFileInfo>
     */
    private $defaultConfigFileInfos;

    public function __construct(string $configDirectoryPath)
    {
        $defaultConfigFileInfos = [];
        foreach (self::DEFAULT_EXTENSIONS as $defaultExtension) {
            $rectorRoot = $configDirectoryPath . sprintf('/../vendor/rector/%s/config/config.php', $defaultExtension);
            $rectorSubPackage = $configDirectoryPath . sprintf('/../../%s/config/config.php', $defaultExtension);

            foreach ([$rectorRoot, $rectorSubPackage] as $possibleFilePath) {
                if (! file_exists($possibleFilePath)) {
                    continue;
                }

                $packageName = sprintf('rector/%s', $defaultExtension);

                $defaultConfigFileInfos[$packageName] = new SmartFileInfo($possibleFilePath);
            }
        }

        $this->defaultConfigFileInfos = $defaultConfigFileInfos;
    }

    /**
     * @return array<string, SmartFileInfo>
     */
    public function provide(): array
    {
        $configFileInfos = $this->defaultConfigFileInfos;

        if (! class_exists('Rector\RectorInstaller\GeneratedConfig')) {
            return $configFileInfos;
        }

        $generatedConfigReflectionClass = new ReflectionClass('Rector\RectorInstaller\GeneratedConfig');
        if ($generatedConfigReflectionClass->getFileName() === false) {
            return $configFileInfos;
        }

        $generatedConfigDirectory = dirname($generatedConfigReflectionClass->getFileName());
        foreach (GeneratedConfig::EXTENSIONS as $extensionKey => $extensionConfig) {
            foreach ($extensionConfig['extra']['includes'] ?? [] as $includedFile) {
                $includedFilePath = $this->resolveIncludeFilePath(
                    $extensionConfig,
                    $generatedConfigDirectory,
                    $includedFile
                );

                if ($includedFilePath === null) {
                    $includedFilePath = sprintf('%s/%s', $extensionConfig['install_path'], $includedFile);
                }

                $configFileInfos[$extensionKey] = new SmartFileInfo($includedFilePath);
            }
        }

        return $configFileInfos;
    }

    /**
     * @param array<string, mixed> $extensionConfig
     */
    private function resolveIncludeFilePath(
        array $extensionConfig,
        string $generatedConfigDirectory,
        string $includedFile
    ): ?string {
        if (! isset($extensionConfig['relative_install_path'])) {
            return null;
        }

        $includedFilePath = sprintf(
            '%s/%s/%s',
            $generatedConfigDirectory,
            $extensionConfig['relative_install_path'],
            $includedFile
        );
        if (! file_exists($includedFilePath)) {
            return null;
        }
        if (! is_readable($includedFilePath)) {
            return null;
        }
        return $includedFilePath;
    }
}
