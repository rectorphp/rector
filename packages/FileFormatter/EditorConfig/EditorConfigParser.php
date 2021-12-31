<?php

declare (strict_types=1);
namespace Rector\FileFormatter\EditorConfig;

use RectorPrefix20211231\Idiosyncratic\EditorConfig\EditorConfig;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\EditorConfigOption;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
/**
 * @see \Rector\Tests\FileFormatter\EditorConfig\EditorConfigParser\EditorConfigParserTest
 */
final class EditorConfigParser
{
    /**
     * @readonly
     * @var \Idiosyncratic\EditorConfig\EditorConfig
     */
    private $editorConfig;
    public function __construct(\RectorPrefix20211231\Idiosyncratic\EditorConfig\EditorConfig $editorConfig)
    {
        $this->editorConfig = $editorConfig;
    }
    public function extractConfigurationForFile(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder) : \Rector\FileFormatter\ValueObject\EditorConfigConfiguration
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $configuration = $this->editorConfig->getConfigForPath($smartFileInfo->getRealPath());
        if (\array_key_exists(\Rector\FileFormatter\ValueObject\EditorConfigOption::INDENT_STYLE, $configuration)) {
            $indentStyle = (string) $configuration[\Rector\FileFormatter\ValueObject\EditorConfigOption::INDENT_STYLE]->getValue();
            $editorConfigConfigurationBuilder->withIndentStyle($indentStyle);
        }
        if (\array_key_exists(\Rector\FileFormatter\ValueObject\EditorConfigOption::INDENT_SIZE, $configuration)) {
            $indentSize = (int) $configuration[\Rector\FileFormatter\ValueObject\EditorConfigOption::INDENT_SIZE]->getValue();
            $editorConfigConfigurationBuilder->withIndentSize($indentSize);
        }
        if (\array_key_exists(\Rector\FileFormatter\ValueObject\EditorConfigOption::END_OF_LINE, $configuration)) {
            $endOfLine = (string) $configuration[\Rector\FileFormatter\ValueObject\EditorConfigOption::END_OF_LINE]->getValue();
            $editorConfigConfigurationBuilder->withEndOfLineFromEditorConfig($endOfLine);
        }
        if (\array_key_exists(\Rector\FileFormatter\ValueObject\EditorConfigOption::INSERT_FINAL_NEWLINE, $configuration)) {
            $insertFinalNewline = (bool) $configuration[\Rector\FileFormatter\ValueObject\EditorConfigOption::INSERT_FINAL_NEWLINE]->getValue();
            $editorConfigConfigurationBuilder->withInsertFinalNewline($insertFinalNewline);
        }
        return $editorConfigConfigurationBuilder->build();
    }
}
