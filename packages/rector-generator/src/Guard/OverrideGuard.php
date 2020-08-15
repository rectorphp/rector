<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Guard;

use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\ValueObject\RectorRecipeConfiguration;
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

    public function __construct(SymfonyStyle $symfonyStyle, TemplateFileSystem $templateFileSystem)
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
        RectorRecipeConfiguration $rectorRecipeConfiguration,
        string $targetDirectory
    ): bool {
        foreach ($templateFileInfos as $templateFileInfo) {
            if (! $this->doesFileInfoAlreadyExist(
                $templateVariables,
                $rectorRecipeConfiguration,
                $templateFileInfo,
                $targetDirectory
            )) {
                continue;
            }

            return ! $this->symfonyStyle->confirm('Files for this rule already exist. Should we override them?');
        }

        return false;
    }

    private function doesFileInfoAlreadyExist(
        array $templateVariables,
        RectorRecipeConfiguration $rectorRecipeConfiguration,
        SmartFileInfo $templateFileInfo,
        string $targetDirectory
    ): bool {
        $destination = $this->templateFileSystem->resolveDestination(
            $templateFileInfo,
            $templateVariables,
            $rectorRecipeConfiguration,
            $targetDirectory
        );

        return file_exists($destination);
    }
}
