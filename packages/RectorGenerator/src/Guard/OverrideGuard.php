<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Guard;

use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\ValueObject\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OverrideGuard
{
    /**
     * @var TemplateFileSystem
     */
    private $templateFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(TemplateFileSystem $templateFileSystem, SymfonyStyle $symfonyStyle)
    {
        $this->templateFileSystem = $templateFileSystem;
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param SmartFileInfo[] $templateFileInfos
     */
    public function isUnwantedOverride(
        array $templateFileInfos,
        array $templateVariables,
        Configuration $configuration
    ) {
        foreach ($templateFileInfos as $templateFileInfo) {
            if (! $this->doesFileInfoAlreadyExist($templateVariables, $configuration, $templateFileInfo)) {
                continue;
            }

            return ! $this->symfonyStyle->confirm('Files for this rules already exist. Should we override them?');
        }

        return false;
    }

    private function doesFileInfoAlreadyExist(
        array $templateVariables,
        Configuration $configuration,
        SmartFileInfo $templateFileInfo
    ): bool {
        $destination = $this->templateFileSystem->resolveDestination(
            $templateFileInfo,
            $templateVariables,
            $configuration
        );

        return file_exists($destination);
    }
}
