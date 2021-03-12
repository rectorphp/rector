<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class PrefixedEol
{
    public function run(string $format)
    {
        return "prefix $format\n";
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class PrefixedEol
{
    public function run(string $format)
    {
        return sprintf('prefix %s%s', $format, PHP_EOL);
    }
}

?>
