<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Newline
{
    public function run(string $format)
    {
        return $format . PHP_EOL;
    }
}

?>
