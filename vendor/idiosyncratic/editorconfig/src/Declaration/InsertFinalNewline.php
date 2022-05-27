<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Idiosyncratic\EditorConfig\Declaration;

final class InsertFinalNewline extends BooleanDeclaration
{
    public function getName() : string
    {
        return 'insert_final_newline';
    }
}
