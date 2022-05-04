<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

final class SkipEscapedQuote
{
    public function test_escaped_double_quote($content)
    {
        return preg_replace("/src(=[\"'].*)/U", '', $content);
    }
}
