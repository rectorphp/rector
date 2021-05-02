<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use PrettyXml\Formatter;
use Rector\Core\Contract\Formatter\FileFormatterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;

final class XmlFileFormatter implements FileFormatterInterface
{
    /**
     * @var Formatter
     */
    private $xmlFormatter;

    public function __construct(Formatter $xmlFormatter)
    {
        $this->xmlFormatter = $xmlFormatter;
    }

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        return in_array($smartFileInfo->getExtension(), ['xml'], true);
    }

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void
    {
        $this->xmlFormatter->setIndentCharacter($editorConfigConfiguration->getIndentStyleCharacter());
        $this->xmlFormatter->setIndentSize($editorConfigConfiguration->getIndentSize());
        $newFileContent = $this->xmlFormatter->format($file->getFileContent());

        $file->changeFileContent($newFileContent);
    }

    public function createEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withLineFeed();
        $editorConfigConfigurationBuilder->withTab();
        $editorConfigConfigurationBuilder->withTabWidth(1);
        $editorConfigConfigurationBuilder->withFinalNewline();

        return $editorConfigConfigurationBuilder;
    }
}
