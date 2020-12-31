<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Newline2
{
    public function run(string $format)
    {
        return "\n${format}";
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Newline2
{
    public function run(string $format)
    {
        return PHP_EOL . $format;
    }
}

?>
