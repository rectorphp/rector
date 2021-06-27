<?php

declare (strict_types=1);
namespace RectorPrefix20210627\Idiosyncratic\EditorConfig\Declaration;

final class InsertFinalNewline extends \RectorPrefix20210627\Idiosyncratic\EditorConfig\Declaration\BooleanDeclaration
{
    public function getName() : string
    {
        return 'insert_final_newline';
    }
}
