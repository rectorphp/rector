<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipIssue5033
{
    public function run($themes = [])
    {
        $url = 'https://example.com/';
        $api_key = MY_API_KEY ?? null;
        //$term = $term;
        $url = $url.'?'.http_build_query(['key' => $api_key]);
    }
}
