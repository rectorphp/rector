<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\MagicEventDispatcher;

final class SkipCommentedParamFuncGetArgs
{
    public function run()
    {
        $magicEventDispatcher = new MagicEventDispatcher();
        $magicEventDispatcher->dispatch(1, 2);
    }
}
