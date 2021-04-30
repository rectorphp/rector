<?php

namespace Rector\Core\Contract\EditorConfig;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\EditorConfigConfiguration;
use Rector\Core\ValueObjectFactory\EditorConfigConfigurationBuilder;

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

    public function extractConfigurationForFile(
        File $file,
        EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder
    ): EditorConfigConfiguration;
}
