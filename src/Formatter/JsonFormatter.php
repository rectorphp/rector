<?php
declare(strict_types=1);

namespace Rector\Core\Formatter;

use Ergebnis\Json\Printer\Printer;
use Rector\Core\Contract\Formatter\FormatterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\EditorConfigConfiguration;
use Rector\Core\ValueObjectFactory\EditorConfigConfigurationBuilder;

/**
 * @see \Rector\Core\Tests\Formatter\JsonFormatter\JsonFormatterTest
 */
final class JsonFormatter implements FormatterInterface
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
        $newContent = $this->jsonPrinter->print(
            $file->getFileContent(),
            $editorConfigConfiguration->getIndent(),
            $editorConfigConfiguration->getEndOfLine()
        );

        $newContent .= $editorConfigConfiguration->getFinalNewline();

        $file->changeFileContent($newContent);
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
