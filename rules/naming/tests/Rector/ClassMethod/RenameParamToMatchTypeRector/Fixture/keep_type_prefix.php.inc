<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node\Expr\FuncCall;

final class KeepTypePrefix
{
    private function run(FuncCall $sprintfFuncCall): array
    {

    }
}
