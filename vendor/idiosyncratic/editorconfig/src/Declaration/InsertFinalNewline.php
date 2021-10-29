<?php

declare (strict_types=1);
namespace RectorPrefix20211029\Idiosyncratic\EditorConfig\Declaration;

final class InsertFinalNewline extends \RectorPrefix20211029\Idiosyncratic\EditorConfig\Declaration\BooleanDeclaration
{
    public function getName() : string
    {
        return 'insert_final_newline';
    }
}
