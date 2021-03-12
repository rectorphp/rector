<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

class SkipParseUrl
{
    public function run()
    {
        return array_merge($this->parseUrl($url), $this->parseUrl($redirectLocation));
    }

    /**
     * @return array<string, string>
     */
    private function parseUrl(string $url): array
    {
        $urlParts = parse_url($url);

        return array_filter($urlParts);
    }
}
