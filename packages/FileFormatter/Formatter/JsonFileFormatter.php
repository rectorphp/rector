<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use Ergebnis\Json\Printer\Printer;
use Rector\Core\Contract\Formatter\FileFormatterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;

/**
 * @see \Rector\Core\Tests\Formatter\JsonFormatter\JsonFormatterTest
 */
final class JsonFileFormatter implements FileFormatterInterface
{
    /**
     * @var Printer
     */
    private $jsonPrinter;

    public function __construct(Printer $jsonPrinter)
    {
        $this->jsonPrinter = $jsonPrinter;
    }

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        return in_array($smartFileInfo->getExtension(), ['json'], true);
    }

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void
    {
        $newFileContent = $this->jsonPrinter->print(
            $file->getFileContent(),
            $editorConfigConfiguration->getIndent(),
            $editorConfigConfiguration->getEndOfLine()
        );

        $newFileContent .= $editorConfigConfiguration->getFinalNewline();

        $file->changeFileContent($newFileContent);
    }

    public function createEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withLineFeed();
        $editorConfigConfigurationBuilder->withSpace();
        $editorConfigConfigurationBuilder->withIndentSize(4);
        $editorConfigConfigurationBuilder->withFinalNewline();

        return $editorConfigConfigurationBuilder;
    }
}
