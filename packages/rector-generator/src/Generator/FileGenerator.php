<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Generator;

use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\ValueObject\Configuration;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FileGenerator
{
    /**
     * @var TemplateFileSystem
     */
    private $templateFileSystem;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        SmartFileSystem $smartFileSystem,
        TemplateFactory $templateFactory,
        TemplateFileSystem $templateFileSystem
    ) {
        $this->templateFileSystem = $templateFileSystem;
        $this->templateFactory = $templateFactory;
        $this->smartFileSystem = $smartFileSystem;
    }

    /**
     * @param string[] $templateVariables
     * @return string[]
     */
    public function generateFiles(
        array $templateFileInfos,
        array $templateVariables,
        Configuration $configuration,
        string $destinationDirectory
    ): array {
        $generatedFilePaths = [];

        foreach ($templateFileInfos as $fileInfo) {
            $generatedFilePaths[] = $this->generateFileInfoWithTemplateVariables(
                $fileInfo,
                $templateVariables,
                $configuration->getPackage(),
                $destinationDirectory
            );
        }

        return $generatedFilePaths;
    }

    private function generateFileInfoWithTemplateVariables(
        SmartFileInfo $smartFileInfo,
        array $templateVariables,
        string $package,
        string $targetDirectory
    ): string {
        $targetFilePath = $this->templateFileSystem->resolveDestination(
            $smartFileInfo,
            $templateVariables,
            $package,
            $targetDirectory
        );

        $content = $this->templateFactory->create($smartFileInfo->getContents(), $templateVariables);
        $this->smartFileSystem->dumpFile($targetFilePath, $content);

        return $targetFilePath;
    }
}
