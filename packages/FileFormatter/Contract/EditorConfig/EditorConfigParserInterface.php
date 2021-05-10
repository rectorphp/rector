<?php

namespace Rector\FileFormatter\Contract\EditorConfig;

use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
interface EditorConfigParserInterface
{
    /**
     * @var string
     */
    public const INDENT_STYLE = 'indent_style';
    /**
     * @var string
     */
    public const INDENT_SIZE = 'indent_size';
    /**
     * @var string
     */
    public const END_OF_LINE = 'end_of_line';
    /**
     * @var string
     */
    public const INSERT_FINAL_NEWLINE = 'insert_final_newline';
    /**
     * @var string
     */
    public const TAB_WIDTH = 'tab_width';
    public function extractConfigurationForFile(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder) : \Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
}
