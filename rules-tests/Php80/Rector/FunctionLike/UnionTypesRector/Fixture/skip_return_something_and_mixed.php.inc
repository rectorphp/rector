<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipReturnSomethingAndMixed
{
    /**
     * @return mixed|string
     */
    private function getMessage()
    {
    }
}
