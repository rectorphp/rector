<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Formatter;

use RectorPrefix20210514\PrettyXml\Formatter;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
/**
 * @see \Rector\Tests\FileFormatter\Formatter\XmlFileFormatter\XmlFileFormatterTest
 */
final class XmlFileFormatter implements \Rector\FileFormatter\Contract\Formatter\FileFormatterInterface
{
    /**
     * @var \PrettyXml\Formatter
     */
    private $xmlFormatter;
    public function __construct(\RectorPrefix20210514\PrettyXml\Formatter $xmlFormatter)
    {
        $this->xmlFormatter = $xmlFormatter;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getExtension() === 'xml';
    }
    public function format(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : void
    {
        $this->xmlFormatter->setIndentCharacter($editorConfigConfiguration->getIndentStyleCharacter());
        $this->xmlFormatter->setIndentSize($editorConfigConfiguration->getIndentSize());
        $newFileContent = $this->xmlFormatter->format($file->getFileContent());
        $newFileContent .= $editorConfigConfiguration->getFinalNewline();
        $file->changeFileContent($newFileContent);
    }
    public function createDefaultEditorConfigConfigurationBuilder() : \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = new \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(\Rector\FileFormatter\ValueObject\Indent::createTabWithSize(1));
        return $editorConfigConfigurationBuilder;
    }
}
