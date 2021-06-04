<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use Nette\Utils\Strings;
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
    /**
     * @see https://regex101.com/r/uTmMcr/1
     * @var string
     */
    private const XML_PARTS_REGEX = '#(>)(<)(\/*)#';

    /**
     * @see https://regex101.com/r/hSG1JT/1
     * @var string
     */
    private const IS_OPENING_TAG_REGEX = '#^<[^\/]*>$#';

    /**
     * @see https://regex101.com/r/ywS62K/1
     * @var string
     */
    private const IS_CLOSING_TAG_REGEX = '#^\s*<\/#';

    private ?int $depth = null;

    private int $indent = 4;

    private string $padChar = ' ';

    private bool $preserveWhitespace = false;

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        return $smartFileInfo->getExtension() === 'xml';
    }

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void
    {
        $this->padChar = $editorConfigConfiguration->getIndentStyleCharacter();
        $this->indent = $editorConfigConfiguration->getIndentSize();

        $newFileContent = $this->formatXml($file->getFileContent(), $editorConfigConfiguration);

        $newFileContent .= $editorConfigConfiguration->getFinalNewline();

        $file->changeFileContent($newFileContent);
    }

    public function createDefaultEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();

        $editorConfigConfigurationBuilder->withIndent(Indent::createTab());

        return $editorConfigConfigurationBuilder;
    }

    private function formatXml(string $xml, EditorConfigConfiguration $editorConfigConfiguration): string
    {
        $output = '';
        $this->depth = 0;

        $parts = $this->getXmlParts($xml);

        if (str_starts_with($parts[0], '<?xml')) {
            $output = array_shift($parts) . $editorConfigConfiguration->getNewline();
        }

        foreach ($parts as $part) {
            $output .= $this->getOutputForPart($part, $editorConfigConfiguration);
        }

        return trim($output);
    }

    /**
     * @return string[]
     */
    private function getXmlParts(string $xml): array
    {
        $withNewLines = Strings::replace(trim($xml), self::XML_PARTS_REGEX, "$1\n$2$3");
        return explode("\n", $withNewLines);
    }

    private function getOutputForPart(string $part, EditorConfigConfiguration $editorConfigConfiguration): string
    {
        $output = '';
        $this->runPre($part);

        if ($this->preserveWhitespace) {
            $output .= $part . $editorConfigConfiguration->getNewline();
        } else {
            $part = trim($part);
            $output .= $this->getPaddedString($part) . $editorConfigConfiguration->getNewline();
        }

        $this->runPost($part);

        return $output;
    }

    private function runPre(string $part): void
    {
        if ($this->isClosingTag($part)) {
            --$this->depth;
        }
    }

    private function runPost(string $part): void
    {
        if ($this->isOpeningTag($part)) {
            ++$this->depth;
        }
        if ($this->isClosingCdataTag($part)) {
            $this->preserveWhitespace = false;
        }
        if ($this->isOpeningCdataTag($part)) {
            $this->preserveWhitespace = true;
        }
    }

    private function getPaddedString(string $part): string
    {
        return str_pad($part, strlen($part) + ($this->depth * $this->indent), $this->padChar, STR_PAD_LEFT);
    }

    private function isOpeningTag(string $part): bool
    {
        return (bool) Strings::match($part, self::IS_OPENING_TAG_REGEX);
    }

    private function isClosingTag(string $part): bool
    {
        return (bool) Strings::match($part, self::IS_CLOSING_TAG_REGEX);
    }

    private function isOpeningCdataTag(string $part): bool
    {
        return \str_contains($part, '<![CDATA[');
    }

    private function isClosingCdataTag(string $part): bool
    {
        return \str_contains($part, ']]>');
    }
}
