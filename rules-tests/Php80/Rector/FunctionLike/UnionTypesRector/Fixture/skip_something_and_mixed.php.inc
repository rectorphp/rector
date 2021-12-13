<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipSomethingAndMixed
{
    /**
     * @param mixed|string $message
     */
    private function getMessage($message)
    {
    }
}
