<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\MagicEventDispatcher;

final class SkipCommentedParamFuncGetArgs
{
    public function run()
    {
        $magicEventDispatcher = new MagicEventDispatcher();
        $magicEventDispatcher->dispatch(1, 2);
    }
}
