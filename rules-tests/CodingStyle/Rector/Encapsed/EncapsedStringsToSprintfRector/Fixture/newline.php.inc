<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Newline
{
    public function run(string $format)
    {
        return "${format}\n";
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Newline
{
    public function run(string $format)
    {
        return $format . PHP_EOL;
    }
}

?>
