<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

final class TrimTrailingWhitespace extends BooleanDeclaration
{
    public function getName() : string
    {
        return 'trim_trailing_whitespace';
    }
}
