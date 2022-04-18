<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Formatter;

use RectorPrefix20220418\Ergebnis\Json\Printer\PrinterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
/**
 * @see \Rector\Tests\FileFormatter\Formatter\JsonFileFormatter\JsonFileFormatterTest
 */
final class JsonFileFormatter implements \Rector\FileFormatter\Contract\Formatter\FileFormatterInterface
{
    /**
     * @readonly
     * @var \Ergebnis\Json\Printer\PrinterInterface
     */
    private $jsonPrinter;
    public function __construct(\RectorPrefix20220418\Ergebnis\Json\Printer\PrinterInterface $jsonPrinter)
    {
        $this->jsonPrinter = $jsonPrinter;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getExtension() === 'json';
    }
    public function format(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : void
    {
        $newFileContent = $this->jsonPrinter->print($file->getFileContent(), $editorConfigConfiguration->getIndent(), $editorConfigConfiguration->getNewLine());
        $newFileContent .= $editorConfigConfiguration->getFinalNewline();
        $file->changeFileContent($newFileContent);
    }
    public function createDefaultEditorConfigConfigurationBuilder() : \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = new \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(\Rector\FileFormatter\ValueObject\Indent::createSpaceWithSize(4));
        return $editorConfigConfigurationBuilder;
    }
}
