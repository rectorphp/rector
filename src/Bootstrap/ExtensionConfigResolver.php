<?php
declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Symplify\SmartFileSystem\SmartFileInfo;

final class ExtensionConfigResolver
{
    /**
     * @param SmartFileInfo[] $configFileInfos
     * @return SmartFileInfo[]
     */
    public function appendExtensionsConfig(array $configFileInfos): array
    {
        if (! class_exists('Rector\RectorInstaller\GeneratedConfig')) {
            return $configFileInfos;
        }

        $generatedConfigReflection = new \ReflectionClass('Rector\RectorInstaller\GeneratedConfig');

        if ($generatedConfigReflection->getFileName() === false) {
            return $configFileInfos;
        }

        $generatedConfigDirectory = dirname($generatedConfigReflection->getFileName());
        foreach (\Rector\RectorInstaller\GeneratedConfig::EXTENSIONS as $name => $extensionConfig) {
            foreach ($extensionConfig['extra']['includes'] ?? [] as $includedFile) {
                $includedFilePath = null;
                if (isset($extensionConfig['relative_install_path'])) {
                    $includedFilePath = sprintf(
                        '%s/%s/%s',
                        $generatedConfigDirectory,
                        $extensionConfig['relative_install_path'],
                        $includedFile
                    );
                    if (! file_exists($includedFilePath) || ! is_readable($includedFilePath)) {
                        $includedFilePath = null;
                    }
                }

                if ($includedFilePath === null) {
                    $includedFilePath = sprintf('%s/%s', $extensionConfig['install_path'], $includedFile);
                }
                $configFileInfos[] = new SmartFileInfo($includedFilePath);
            }
        }

        return $configFileInfos;
    }
}
