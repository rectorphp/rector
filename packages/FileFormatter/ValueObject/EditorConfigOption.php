<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObject;

final class EditorConfigOption
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
}
