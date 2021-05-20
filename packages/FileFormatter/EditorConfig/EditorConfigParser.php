<?php

declare(strict_types=1);

namespace Rector\FileFormatter\EditorConfig;

use Idiosyncratic\EditorConfig\EditorConfig;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\EditorConfigOption;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;

/**
 * @see \Rector\Tests\FileFormatter\EditorConfig\EditorConfigParser\EditorConfigParserTest
 */
final class EditorConfigParser
{
    public function __construct(
        private EditorConfig $editorConfig
    ) {
    }

    public function extractConfigurationForFile(
        File $file,
        EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder
    ): EditorConfigConfiguration {
        $smartFileInfo = $file->getSmartFileInfo();
        $configuration = $this->editorConfig->getConfigForPath($smartFileInfo->getRealPath());

        if (array_key_exists(EditorConfigOption::INDENT_STYLE, $configuration)) {
            $indentStyle = (string) $configuration[EditorConfigOption::INDENT_STYLE]->getValue();

            $editorConfigConfigurationBuilder->withIndentStyle($indentStyle);
        }

        if (array_key_exists(EditorConfigOption::INDENT_SIZE, $configuration)) {
            $indentSize = (int) $configuration[EditorConfigOption::INDENT_SIZE]->getValue();

            $editorConfigConfigurationBuilder->withIndentSize($indentSize);
        }

        if (array_key_exists(EditorConfigOption::END_OF_LINE, $configuration)) {
            $endOfLine = (string) $configuration[EditorConfigOption::END_OF_LINE]->getValue();

            $editorConfigConfigurationBuilder->withEndOfLineFromEditorConfig($endOfLine);
        }

        if (array_key_exists(EditorConfigOption::INSERT_FINAL_NEWLINE, $configuration)) {
            $insertFinalNewline = (bool) $configuration[EditorConfigOption::INSERT_FINAL_NEWLINE]->getValue();

            $editorConfigConfigurationBuilder->withInsertFinalNewline($insertFinalNewline);
        }

        return $editorConfigConfigurationBuilder->build();
    }
}
