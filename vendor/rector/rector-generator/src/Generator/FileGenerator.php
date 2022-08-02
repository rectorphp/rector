<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\Generator;

use RectorPrefix202208\Nette\Utils\Strings;
use Rector\RectorGenerator\Enum\Packages;
use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
final class FileGenerator
{
    /**
     * @var string
     * @see https://regex101.com/r/RVbPEX/1
     */
    public const RECTOR_UTILS_REGEX = '#Rector\\\\Utils#';
    /**
     * @var string
     * @see https://regex101.com/r/RVbPEX/1
     */
    public const RECTOR_UTILS_TESTS_REGEX = '#Rector\\\\Tests\\\\Utils#';
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\TemplateFactory
     */
    private $templateFactory;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\FileSystem\TemplateFileSystem
     */
    private $templateFileSystem;
    public function __construct(SmartFileSystem $smartFileSystem, TemplateFactory $templateFactory, TemplateFileSystem $templateFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->templateFactory = $templateFactory;
        $this->templateFileSystem = $templateFileSystem;
    }
    /**
     * @param SmartFileInfo[] $templateFileInfos
     * @param array<string, string> $templateVariables
     * @return string[]
     */
    public function generateFiles(array $templateFileInfos, array $templateVariables, RectorRecipe $rectorRecipe, string $destinationDirectory) : array
    {
        $generatedFilePaths = [];
        foreach ($templateFileInfos as $templateFileInfo) {
            $generatedFilePaths[] = $this->generateFileInfoWithTemplateVariables($templateFileInfo, $templateVariables, $rectorRecipe, $destinationDirectory);
        }
        return $generatedFilePaths;
    }
    /**
     * @param array<string, string> $templateVariables
     */
    private function generateFileInfoWithTemplateVariables(SmartFileInfo $smartFileInfo, array $templateVariables, RectorRecipe $rectorRecipe, string $targetDirectory) : string
    {
        $targetFilePath = $this->templateFileSystem->resolveDestination($smartFileInfo, $templateVariables, $rectorRecipe, $targetDirectory);
        $content = $this->templateFactory->create($smartFileInfo->getContents(), $templateVariables);
        // replace "Rector\Utils\" with "Utils\Rector\" for 3rd party packages
        if (!$rectorRecipe->isRectorRepository()) {
            $content = Strings::replace($content, self::RECTOR_UTILS_REGEX, 'Utils\\Rector');
            $content = Strings::replace($content, self::RECTOR_UTILS_TESTS_REGEX, 'Utils\\Rector\\Tests');
        }
        // correct tests PSR-4 namespace for core rector packages
        if (\in_array($rectorRecipe->getPackage(), Packages::RECTOR_CORE, \true)) {
            $content = Strings::replace($content, '#namespace Rector\\\\Tests\\\\' . $rectorRecipe->getPackage() . '#', 'namespace Rector\\' . $rectorRecipe->getPackage() . '\\Tests');
            // add core package main config
            if (\substr_compare($targetFilePath, 'configured_rule.php', -\strlen('configured_rule.php')) === 0) {
                $rectorConfigLine = 'return static function (RectorConfig $rectorConfig): void {';
                $content = \str_replace($rectorConfigLine, $rectorConfigLine . \PHP_EOL . '    $rectorConfig->import(__DIR__ . \'/../../../../../config/config.php\');' . \PHP_EOL, $content);
            }
        }
        $this->smartFileSystem->dumpFile($targetFilePath, $content);
        return $targetFilePath;
    }
}
