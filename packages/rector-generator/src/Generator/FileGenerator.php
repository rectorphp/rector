<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Generator;

use Nette\Utils\Strings;
use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FileGenerator
{
    /**
     * @var string
     * @see https://regex101.com/r/RVbPEX/1
     */
    public const RECTOR_UTILS_REGEX = '#Rector\\\\Utils#';

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
     * @param SmartFileInfo[] $templateFileInfos
     * @param string[] $templateVariables
     * @return string[]
     */
    public function generateFiles(
        array $templateFileInfos,
        array $templateVariables,
        RectorRecipe $rectorRecipe,
        string $destinationDirectory
    ): array {
        $generatedFilePaths = [];

        foreach ($templateFileInfos as $fileInfo) {
            $generatedFilePaths[] = $this->generateFileInfoWithTemplateVariables(
                $fileInfo,
                $templateVariables,
                $rectorRecipe,
                $destinationDirectory
            );
        }

        return $generatedFilePaths;
    }

    /**
     * @param array<string, mixed> $templateVariables
     */
    private function generateFileInfoWithTemplateVariables(
        SmartFileInfo $smartFileInfo,
        array $templateVariables,
        RectorRecipe $rectorRecipe,
        string $targetDirectory
    ): string {
        $targetFilePath = $this->templateFileSystem->resolveDestination(
            $smartFileInfo,
            $templateVariables,
            $rectorRecipe,
            $targetDirectory
        );

        $content = $this->templateFactory->create($smartFileInfo->getContents(), $templateVariables);

        // replace "Rector\Utils\" with "Utils\Rector\" for 3rd party packages
        if (! $rectorRecipe->isRectorRepository()) {
            $content = Strings::replace($content, self::RECTOR_UTILS_REGEX, 'Utils\Rector');
        }

        $this->smartFileSystem->dumpFile($targetFilePath, $content);

        return $targetFilePath;
    }
}
