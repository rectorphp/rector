<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Formatter;

use RectorPrefix20220209\Nette\Utils\Strings;
use Rector\Core\Util\StringUtils;
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
     * @see https://regex101.com/r/uTmMcr/1
     * @var string
     */
    private const XML_PARTS_REGEX = '#(>)(<)(\\/*)#';
    /**
     * @see https://regex101.com/r/hSG1JT/1
     * @var string
     */
    private const IS_OPENING_TAG_REGEX = '#^<[^\\/]*>$#';
    /**
     * @see https://regex101.com/r/ywS62K/1
     * @var string
     */
    private const IS_CLOSING_TAG_REGEX = '#^\\s*<\\/#';
    /**
     * @var int
     */
    private $depth = 0;
    /**
     * @var int
     */
    private $indent = 4;
    /**
     * @var string
     */
    private $padChar = ' ';
    /**
     * @var bool
     */
    private $preserveWhitespace = \false;
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getExtension() === 'xml';
    }
    public function format(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : void
    {
        $this->padChar = $editorConfigConfiguration->getIndentStyleCharacter();
        $this->indent = $editorConfigConfiguration->getIndentSize();
        $newFileContent = $this->formatXml($file->getFileContent(), $editorConfigConfiguration);
        $newFileContent .= $editorConfigConfiguration->getFinalNewline();
        $file->changeFileContent($newFileContent);
    }
    public function createDefaultEditorConfigConfigurationBuilder() : \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = new \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(\Rector\FileFormatter\ValueObject\Indent::createTab());
        return $editorConfigConfigurationBuilder;
    }
    private function formatXml(string $xml, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : string
    {
        $output = '';
        $this->depth = 0;
        $parts = $this->getXmlParts($xml);
        if (\strncmp($parts[0], '<?xml', \strlen('<?xml')) === 0) {
            $output = \array_shift($parts) . $editorConfigConfiguration->getNewLine();
        }
        foreach ($parts as $part) {
            $output .= $this->getOutputForPart($part, $editorConfigConfiguration);
        }
        return \trim($output);
    }
    /**
     * @return string[]
     */
    private function getXmlParts(string $xml) : array
    {
        $withNewLines = \RectorPrefix20220209\Nette\Utils\Strings::replace(\trim($xml), self::XML_PARTS_REGEX, "\$1\n\$2\$3");
        return \explode("\n", $withNewLines);
    }
    private function getOutputForPart(string $part, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : string
    {
        $output = '';
        $this->runPre($part);
        if ($this->preserveWhitespace) {
            $output .= $part . $editorConfigConfiguration->getNewLine();
        } else {
            $part = \trim($part);
            $output .= $this->getPaddedString($part) . $editorConfigConfiguration->getNewLine();
        }
        $this->runPost($part);
        return $output;
    }
    private function runPre(string $part) : void
    {
        if ($this->isClosingTag($part)) {
            --$this->depth;
        }
    }
    private function runPost(string $part) : void
    {
        if ($this->isOpeningTag($part)) {
            ++$this->depth;
        }
        if ($this->isClosingCdataTag($part)) {
            $this->preserveWhitespace = \false;
        }
        if ($this->isOpeningCdataTag($part)) {
            $this->preserveWhitespace = \true;
        }
    }
    private function getPaddedString(string $part) : string
    {
        return \str_pad($part, \strlen($part) + $this->depth * $this->indent, $this->padChar, \STR_PAD_LEFT);
    }
    private function isOpeningTag(string $part) : bool
    {
        return \Rector\Core\Util\StringUtils::isMatch($part, self::IS_OPENING_TAG_REGEX);
    }
    private function isClosingTag(string $part) : bool
    {
        return \Rector\Core\Util\StringUtils::isMatch($part, self::IS_CLOSING_TAG_REGEX);
    }
    private function isOpeningCdataTag(string $part) : bool
    {
        return \strpos($part, '<![CDATA[') !== \false;
    }
    private function isClosingCdataTag(string $part) : bool
    {
        return \strpos($part, ']]>') !== \false;
    }
}
