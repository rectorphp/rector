<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use PrettyXml\Formatter;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;

/**
 * @see \Rector\Tests\FileFormatter\Formatter\XmlFileFormatter\XmlFileFormatterTest
 */
final class XmlFileFormatter implements FileFormatterInterface
{
    public function __construct(
        private Formatter $xmlFormatter
    ) {
    }

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        return $smartFileInfo->getExtension() === 'xml';
    }

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void
    {
        $this->xmlFormatter->setIndentCharacter($editorConfigConfiguration->getIndentStyleCharacter());
        $this->xmlFormatter->setIndentSize($editorConfigConfiguration->getIndentSize());

        $newFileContent = $this->xmlFormatter->format($file->getFileContent());

        $newFileContent .= $editorConfigConfiguration->getFinalNewline();

        $file->changeFileContent($newFileContent);
    }

    public function createDefaultEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();

        $editorConfigConfigurationBuilder->withIndent(Indent::createTabWithSize(1));

        return $editorConfigConfigurationBuilder;
    }
}
