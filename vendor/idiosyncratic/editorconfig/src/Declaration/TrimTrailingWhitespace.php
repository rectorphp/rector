<?php

declare (strict_types=1);
namespace RectorPrefix20210809\Idiosyncratic\EditorConfig\Declaration;

final class TrimTrailingWhitespace extends \RectorPrefix20210809\Idiosyncratic\EditorConfig\Declaration\BooleanDeclaration
{
    public function getName() : string
    {
        return 'trim_trailing_whitespace';
    }
}
