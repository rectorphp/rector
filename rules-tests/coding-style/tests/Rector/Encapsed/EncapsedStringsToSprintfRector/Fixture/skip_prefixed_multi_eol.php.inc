<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class SkipPrefixedMultiEol
{
    public function run(string $format)
    {
        return "prefix {$format}\n\n";
    }
}

?>
