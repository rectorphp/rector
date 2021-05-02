<?php

declare(strict_types=1);

namespace Rector\FileFormatter\EditorConfig;

use Idiosyncratic\EditorConfig\EditorConfig;
use Rector\Core\Contract\EditorConfig\EditorConfigParserInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;

/**
 * @see \Rector\Core\Tests\EditorConfig\EditorConfigIdiosyncraticParser\EditorConfigIdiosyncraticParserTest
 */
final class EditorConfigIdiosyncraticParser implements EditorConfigParserInterface
{
    /**
     * @var EditorConfig
     */
    private $editorConfig;

    public function __construct(EditorConfig $editorConfig)
    {
        $this->editorConfig = $editorConfig;
    }

    public function extractConfigurationForFile(
        File $file,
        EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder
    ): EditorConfigConfiguration {
        $smartFileInfo = $file->getSmartFileInfo();
        $configuration = $this->editorConfig->getConfigForPath($smartFileInfo->getRealPath());

        if (array_key_exists(self::INDENT_STYLE, $configuration)) {
            $editorConfigConfigurationBuilder->withIndentStyle($configuration[self::INDENT_STYLE]->getValue());
        }

        if (array_key_exists(self::INDENT_SIZE, $configuration)) {
            $editorConfigConfigurationBuilder->withIndentSize($configuration[self::INDENT_SIZE]->getValue());
        }

        if (array_key_exists(self::END_OF_LINE, $configuration)) {
            $editorConfigConfigurationBuilder->withEndOfLine($configuration[self::END_OF_LINE]->getValue());
        }

        if (array_key_exists(self::INSERT_FINAL_NEWLINE, $configuration)) {
            $editorConfigConfigurationBuilder->withInsertFinalNewline(
                $configuration[self::INSERT_FINAL_NEWLINE]->getValue()
            );
        }

        if (array_key_exists(self::TAB_WIDTH, $configuration)) {
            $editorConfigConfigurationBuilder->withTabWidth($configuration[self::TAB_WIDTH]->getValue());
        }

        return $editorConfigConfigurationBuilder->build();
    }
}
