<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipLiteralValueStringUnionType
{
    /**
     * @param 'a' $message
     */
    private function getMessage(string $message): string
    {
        return $message;
    }
}
