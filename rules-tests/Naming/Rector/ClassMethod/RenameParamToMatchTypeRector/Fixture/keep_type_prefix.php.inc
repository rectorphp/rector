<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node\Expr\FuncCall;

final class KeepTypePrefix
{
    private function run(FuncCall $sprintfFuncCall): array
    {

    }
}
