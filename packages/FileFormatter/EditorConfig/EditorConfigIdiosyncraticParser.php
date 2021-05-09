<?php

declare (strict_types=1);
namespace Rector\FileFormatter\EditorConfig;

use RectorPrefix20210509\Idiosyncratic\EditorConfig\EditorConfig;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\EditorConfig\EditorConfigParserInterface;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
/**
 * @see \Rector\Tests\FileFormatter\EditorConfig\EditorConfigIdiosyncraticParser\EditorConfigIdiosyncraticParserTest
 */
final class EditorConfigIdiosyncraticParser implements \Rector\FileFormatter\Contract\EditorConfig\EditorConfigParserInterface
{
    /**
     * @var EditorConfig
     */
    private $editorConfig;
    public function __construct(\RectorPrefix20210509\Idiosyncratic\EditorConfig\EditorConfig $editorConfig)
    {
        $this->editorConfig = $editorConfig;
    }
    public function extractConfigurationForFile(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder) : \Rector\FileFormatter\ValueObject\EditorConfigConfiguration
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $configuration = $this->editorConfig->getConfigForPath($smartFileInfo->getRealPath());
        if (\array_key_exists(self::INDENT_STYLE, $configuration)) {
            $indentStyle = (string) $configuration[self::INDENT_STYLE]->getValue();
            $editorConfigConfigurationBuilder->withIndentStyle($indentStyle);
        }
        if (\array_key_exists(self::INDENT_SIZE, $configuration)) {
            $indentSize = (int) $configuration[self::INDENT_SIZE]->getValue();
            $editorConfigConfigurationBuilder->withIndentSize($indentSize);
        }
        if (\array_key_exists(self::END_OF_LINE, $configuration)) {
            $endOfLine = (string) $configuration[self::END_OF_LINE]->getValue();
            $editorConfigConfigurationBuilder->withEndOfLineFromEditorConfig($endOfLine);
        }
        if (\array_key_exists(self::INSERT_FINAL_NEWLINE, $configuration)) {
            $insertFinalNewline = (bool) $configuration[self::INSERT_FINAL_NEWLINE]->getValue();
            $editorConfigConfigurationBuilder->withInsertFinalNewline($insertFinalNewline);
        }
        if (\array_key_exists(self::TAB_WIDTH, $configuration)) {
            $editorConfigConfigurationBuilder->withIndentSize($configuration[self::TAB_WIDTH]->getValue());
        }
        return $editorConfigConfigurationBuilder->build();
    }
}
